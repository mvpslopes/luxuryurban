<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\StockService;

class DashboardController extends Controller
{
    public function index(): void
    {
        $pdo = Database::connection();
        $role = Auth::role();
        $userId = Auth::id();
        $isAdmin = Auth::isAdmin();

        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        if ($isAdmin) {
            $salesToday = $this->scalarInt(
                'SELECT COUNT(*) FROM sales WHERE status = "concluida" AND DATE(created_at) = ?',
                [$today]
            );
            $revenueToday = $this->scalar(
                'SELECT COALESCE(SUM(total),0) FROM sales WHERE status = "concluida" AND DATE(created_at) = ?',
                [$today]
            );
            $salesMonth = $this->scalarInt(
                'SELECT COUNT(*) FROM sales WHERE status = "concluida" AND created_at >= ?',
                [$monthStart]
            );
            $revenueMonth = $this->scalar(
                'SELECT COALESCE(SUM(total),0) FROM sales WHERE status = "concluida" AND created_at >= ?',
                [$monthStart]
            );
            $pendingApprovals = $this->scalarInt(
                'SELECT COUNT(*) FROM discount_approvals WHERE status = "pendente"'
            );
            $totalCustomers = $this->scalarInt('SELECT COUNT(*) FROM customers');
            $totalProducts = $this->scalarInt('SELECT COUNT(*) FROM products WHERE active = 1');
            $lowStock = (new StockService())->lowStockProducts();

            $chartData = $pdo->query(
                'SELECT DATE(created_at) AS day, COALESCE(SUM(total),0) AS total
                 FROM sales WHERE status = "concluida" AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at) ORDER BY day'
            )->fetchAll();

            $recentSales = $pdo->query(
                'SELECT s.id, s.receipt_number, s.total, s.status, s.payment_method_name, s.created_at,
                        c.name AS customer_name,
                        u.name AS seller_name
                 FROM sales s
                 JOIN customers c ON c.id = s.customer_id
                 JOIN users u ON u.id = s.seller_id
                 ORDER BY s.created_at DESC
                 LIMIT 20'
            )->fetchAll();

            $topSellers = $pdo->prepare(
                'SELECT u.name AS seller_name,
                        COUNT(s.id) AS sales_count,
                        COALESCE(SUM(s.total), 0) AS revenue
                 FROM sales s
                 JOIN users u ON u.id = s.seller_id
                 WHERE s.status = "concluida" AND s.created_at >= ?
                 GROUP BY s.seller_id, u.name
                 ORDER BY revenue DESC
                 LIMIT 5'
            );
            $topSellers->execute([$monthStart]);
            $topSellers = $topSellers->fetchAll();
        } else {
            $salesToday = $this->scalarInt(
                'SELECT COUNT(*) FROM sales WHERE seller_id = ? AND status = "concluida" AND DATE(created_at) = ?',
                [$userId, $today]
            );
            $revenueToday = $this->scalar(
                'SELECT COALESCE(SUM(total),0) FROM sales WHERE seller_id = ? AND status = "concluida" AND DATE(created_at) = ?',
                [$userId, $today]
            );
            $salesMonth = $this->scalarInt(
                'SELECT COUNT(*) FROM sales WHERE seller_id = ? AND status = "concluida" AND created_at >= ?',
                [$userId, $monthStart]
            );
            $revenueMonth = $this->scalar(
                'SELECT COALESCE(SUM(total),0) FROM sales WHERE seller_id = ? AND status = "concluida" AND created_at >= ?',
                [$userId, $monthStart]
            );
            $pendingApprovals = $this->scalarInt(
                'SELECT COUNT(*) FROM sales WHERE seller_id = ? AND status = "pendente_aprovacao"',
                [$userId]
            );
            $totalCustomers = $this->scalarInt('SELECT COUNT(*) FROM customers');
            $totalProducts = 0;
            $lowStock = [];

            $chartStmt = $pdo->prepare(
                'SELECT DATE(created_at) AS day, COALESCE(SUM(total),0) AS total
                 FROM sales WHERE seller_id = ? AND status = "concluida" AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at) ORDER BY day'
            );
            $chartStmt->execute([$userId]);
            $chartData = $chartStmt->fetchAll();

            $recentStmt = $pdo->prepare(
                'SELECT s.id, s.receipt_number, s.total, s.status, s.payment_method_name, s.created_at,
                        c.name AS customer_name,
                        u.name AS seller_name
                 FROM sales s
                 JOIN customers c ON c.id = s.customer_id
                 JOIN users u ON u.id = s.seller_id
                 WHERE s.seller_id = ?
                 ORDER BY s.created_at DESC
                 LIMIT 20'
            );
            $recentStmt->execute([$userId]);
            $recentSales = $recentStmt->fetchAll();

            $topSellers = [];
        }

        $avgTicketToday = $salesToday > 0 ? $revenueToday / $salesToday : 0.0;
        $avgTicketMonth = $salesMonth > 0 ? $revenueMonth / $salesMonth : 0.0;

        $this->view('dashboard/index', compact(
            'salesToday',
            'revenueToday',
            'salesMonth',
            'revenueMonth',
            'pendingApprovals',
            'totalCustomers',
            'totalProducts',
            'lowStock',
            'chartData',
            'role',
            'recentSales',
            'topSellers',
            'avgTicketToday',
            'avgTicketMonth',
            'isAdmin'
        ));
    }

    private function scalar(string $sql, array $params = []): float
    {
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    private function scalarInt(string $sql, array $params = []): int
    {
        return (int) $this->scalar($sql, $params);
    }
}
