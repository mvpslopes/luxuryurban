<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

class StockService
{
    public function getQuantity(int $productId): int
    {
        $stmt = Database::connection()->prepare('SELECT quantity FROM stock WHERE product_id = ?');
        $stmt->execute([$productId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function initialize(int $productId, int $quantity, int $userId, ?string $notes = null): void
    {
        $pdo = Database::connection();
        $pdo->prepare('INSERT INTO stock (product_id, quantity) VALUES (?, ?)')->execute([$productId, $quantity]);

        if ($quantity > 0) {
            $this->recordMovement($productId, 'entrada', $quantity, $quantity, $userId, 'manual', null, $notes ?? 'Estoque inicial');
        }
    }

    public function manualMovement(int $productId, string $type, int $quantity, int $userId, ?string $notes): void
    {
        if (!in_array($type, ['entrada', 'saida', 'ajuste'], true)) {
            throw new \InvalidArgumentException('Tipo de movimentação inválido.');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $current = $this->getQuantity($productId);

            $newBalance = match ($type) {
                'entrada' => $current + $quantity,
                'saida' => $current - $quantity,
                'ajuste' => $quantity,
            };

            if ($newBalance < 0) {
                throw new \RuntimeException('Estoque insuficiente para esta operação.');
            }

            $delta = match ($type) {
                'entrada' => $quantity,
                'saida' => -$quantity,
                'ajuste' => $newBalance - $current,
            };

            $pdo->prepare('UPDATE stock SET quantity = ? WHERE product_id = ?')->execute([$newBalance, $productId]);
            $this->recordMovement($productId, $type, $delta, $newBalance, $userId, 'manual', null, $notes);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function deductForSale(int $productId, int $quantity, int $userId, int $saleId): void
    {
        $pdo = Database::connection();
        $current = $this->getQuantity($productId);

        if ($current < $quantity) {
            throw new \RuntimeException('Estoque insuficiente.');
        }

        $newBalance = $current - $quantity;
        $pdo->prepare('UPDATE stock SET quantity = ? WHERE product_id = ?')->execute([$newBalance, $productId]);
        $this->recordMovement($productId, 'venda', -$quantity, $newBalance, $userId, 'sale', $saleId, 'Baixa por venda');
    }

    public function restoreForRefund(int $productId, int $quantity, int $userId, int $saleId): void
    {
        $pdo = Database::connection();
        $current = $this->getQuantity($productId);
        $newBalance = $current + $quantity;

        $pdo->prepare('UPDATE stock SET quantity = ? WHERE product_id = ?')->execute([$newBalance, $productId]);
        $this->recordMovement($productId, 'estorno', $quantity, $newBalance, $userId, 'sale', $saleId, 'Restauração por estorno');
    }

    private function recordMovement(
        int $productId,
        string $type,
        int $quantity,
        int $balanceAfter,
        int $userId,
        ?string $refType,
        ?int $refId,
        ?string $notes
    ): void {
        $stmt = Database::connection()->prepare(
            'INSERT INTO stock_movements (product_id, type, quantity, balance_after, reference_type, reference_id, notes, user_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$productId, $type, $quantity, $balanceAfter, $refType, $refId, $notes, $userId]);
    }

    public function lowStockProducts(): array
    {
        $stmt = Database::connection()->query(
            'SELECT p.id, p.name, p.sku, p.min_stock, COALESCE(s.quantity, 0) AS quantity
             FROM products p
             LEFT JOIN stock s ON s.product_id = p.id
             WHERE p.active = 1 AND COALESCE(s.quantity, 0) <= p.min_stock
             ORDER BY s.quantity ASC
             LIMIT 10'
        );
        return $stmt->fetchAll();
    }
}
