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

        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        if (Auth::isAdmin()) {
            $salesToday = $this->scalar(
                'SELECT COUNT(*) FROM sales WHERE status = "concluida" AND DATE(created_at) = ?',
                [$today]
            );
            $revenueToday = $this->scalar(
                'SELECT COALESCE(SUM(total),0) FROM sales WHERE status = "concluida" AND DATE(created_at) = ?',
                [$today]
            );
            $revenueMonth = $this->scalar(
                'SELECT COALESCE(SUM(total),0) FROM sales WHERE status = "concluida" AND created_at >= ?',
                [$monthStart]
            );
            $pendingApprovals = $this->scalar(
                'SELECT COUNT(*) FROM discount_approvals WHERE status = "pendente"'
            );
            $totalCustomers = $this->scalar('SELECT COUNT(*) FROM customers');
            $totalProducts = $this->scalar('SELECT COUNT(*) FROM products WHERE active = 1');
            $lowStock = (new StockService())->lowStockProducts();

            $chartData = $pdo->query(
                'SELECT DATE(created_at) AS day, COALESCE(SUM(total),0) AS total
                 FROM sales WHERE status = "concluida" AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at) ORDER BY day'
            )->fetchAll();
        } else {
            $salesToday = $this->scalar(
                'SELECT COUNT(*) FROM sales WHERE seller_id = ? AND status = "concluida" AND DATE(created_at) = ?',
                [$userId, $today]
            );
            $revenueToday = $this->scalar(
                'SELECT COALESCE(SUM(total),0) FROM sales WHERE seller_id = ? AND status = "concluida" AND DATE(created_at) = ?',
                [$userId, $today]
            );
            $revenueMonth = $this->scalar(
                'SELECT COALESCE(SUM(total),0) FROM sales WHERE seller_id = ? AND status = "concluida" AND created_at >= ?',
                [$userId, $monthStart]
            );
            $pendingApprovals = 0;
            $totalCustomers = $this->scalar('SELECT COUNT(*) FROM customers');
            $totalProducts = 0;
            $lowStock = [];
            $chartData = $pdo->prepare(
                'SELECT DATE(created_at) AS day, COALESCE(SUM(total),0) AS total
                 FROM sales WHERE seller_id = ? AND status = "concluida" AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at) ORDER BY day'
            );
            $chartData->execute([$userId]);
            $chartData = $chartData->fetchAll();
        }

        $this->view('dashboard/index', compact(
            'salesToday', 'revenueToday', 'revenueMonth', 'pendingApprovals',
            'totalCustomers', 'totalProducts', 'lowStock', 'chartData', 'role'
        ));
    }

    private function scalar(string $sql, array $params = []): float
    {
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }
}
