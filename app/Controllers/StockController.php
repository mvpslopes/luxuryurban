<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\StockService;

class StockController extends Controller
{
    public function index(): void
    {
        $products = Database::connection()->query(
            'SELECT p.id, p.name, p.sku, p.min_stock, COALESCE(s.quantity, 0) AS quantity, c.name AS category_name
             FROM products p
             JOIN product_categories c ON c.id = p.category_id
             LEFT JOIN stock s ON s.product_id = p.id
             WHERE p.active = 1
             ORDER BY s.quantity ASC, p.name'
        )->fetchAll();

        $this->view('stock/index', compact('products'));
    }

    public function movement(): void
    {
        $products = Database::connection()->query(
            'SELECT p.id, p.name, p.sku, COALESCE(s.quantity,0) AS quantity FROM products p
             LEFT JOIN stock s ON s.product_id = p.id WHERE p.active = 1 ORDER BY p.name'
        )->fetchAll();

        $this->view('stock/movement', compact('products'));
    }

    public function storeMovement(): void
    {
        $this->requirePost();

        $productId = (int) ($_POST['product_id'] ?? 0);
        $type = $_POST['type'] ?? '';
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if (!$productId || !in_array($type, ['entrada', 'saida', 'ajuste'], true)) {
            set_flash('error', 'Dados inválidos.');
            redirect('/estoque/movimentacao');
        }

        try {
            (new StockService())->manualMovement($productId, $type, $quantity, (int) Auth::id(), $notes ?: null);
            set_flash('success', 'Movimentação registrada.');
        } catch (\Throwable $e) {
            set_flash('error', $e->getMessage());
        }

        redirect('/estoque');
    }

    public function history(): void
    {
        $movements = Database::connection()->query(
            'SELECT sm.*, p.name AS product_name, p.sku, u.name AS user_name
             FROM stock_movements sm
             JOIN products p ON p.id = sm.product_id
             JOIN users u ON u.id = sm.user_id
             ORDER BY sm.created_at DESC LIMIT 200'
        )->fetchAll();

        $this->view('stock/history', compact('movements'));
    }
}
