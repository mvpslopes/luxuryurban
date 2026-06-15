<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

class SaleService
{
    private StockService $stock;

    public function __construct()
    {
        $this->stock = new StockService();
    }

    public function generateReceiptNumber(): string
    {
        $pdo = Database::connection();
        $year = (int) date('Y');

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT last_number FROM receipt_sequences WHERE year = ? FOR UPDATE');
            $stmt->execute([$year]);
            $row = $stmt->fetch();

            if (!$row) {
                $pdo->prepare('INSERT INTO receipt_sequences (year, last_number) VALUES (?, 0)')->execute([$year]);
                $next = 1;
            } else {
                $next = (int) $row['last_number'] + 1;
            }

            $pdo->prepare('UPDATE receipt_sequences SET last_number = ? WHERE year = ?')->execute([$next, $year]);
            $pdo->commit();

            return sprintf('LU-%d-%05d', $year, $next);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function calculateDiscount(float $subtotal, string $type, float $value): array
    {
        if ($value <= 0) {
            return ['amount' => 0.0, 'percent' => 0.0];
        }

        if ($type === 'percent') {
            $amount = round($subtotal * ($value / 100), 2);
            return ['amount' => $amount, 'percent' => $value];
        }

        $amount = min($value, $subtotal);
        $percent = $subtotal > 0 ? round(($amount / $subtotal) * 100, 2) : 0;
        return ['amount' => $amount, 'percent' => $percent];
    }

    public function needsApproval(float $discountPercent): bool
    {
        if (Auth::isAdmin()) {
            return false;
        }
        return $discountPercent > config('app')['discount_limit_vendedor'];
    }

    public function createSale(array $data): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $items = $data['items'];
            $subtotal = 0.0;

            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['unit_price'];
                $subtotal += $qty * $price;
            }

            $discount = $this->calculateDiscount(
                $subtotal,
                $data['discount_type'] ?? 'percent',
                (float) ($data['discount_value'] ?? 0)
            );

            $status = 'concluida';
            if ($this->needsApproval($discount['percent'])) {
                $status = 'pendente_aprovacao';
            }

            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                if ($status === 'concluida' && $this->stock->getQuantity((int) $item['product_id']) < $qty) {
                    throw new \RuntimeException('Estoque insuficiente para: ' . $item['product_name']);
                }
            }

            $total = max(0, $subtotal - $discount['amount']);
            $receiptNumber = $this->generateReceiptNumber();

            $stmt = $pdo->prepare(
                'INSERT INTO sales (receipt_number, customer_id, seller_id, subtotal, discount_type, discount_value,
                 discount_amount, total, payment_method_id, payment_method_name, status, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $receiptNumber,
                $data['customer_id'],
                $data['seller_id'],
                $subtotal,
                $data['discount_type'] ?? null,
                $data['discount_value'] ?? 0,
                $discount['amount'],
                $total,
                $data['payment_method_id'],
                $data['payment_method_name'],
                $status,
                $data['notes'] ?? null,
            ]);

            $saleId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO sale_items (sale_id, product_id, product_name, quantity, unit_price, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );

            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['unit_price'];
                $itemStmt->execute([
                    $saleId,
                    $item['product_id'],
                    $item['product_name'],
                    $qty,
                    $price,
                    $qty * $price,
                ]);
            }

            if ($status === 'pendente_aprovacao') {
                $pdo->prepare(
                    'INSERT INTO discount_approvals (sale_id, requested_by, discount_percent, status)
                     VALUES (?, ?, ?, "pendente")'
                )->execute([$saleId, $data['seller_id'], $discount['percent']]);
            }

            if ($status === 'concluida') {
                foreach ($items as $item) {
                    $this->stock->deductForSale(
                        (int) $item['product_id'],
                        (int) $item['quantity'],
                        (int) $data['seller_id'],
                        $saleId
                    );
                }
            }

            $pdo->commit();
            audit_log('sale.created', 'sale', $saleId, ['status' => $status, 'total' => $total]);
            return $saleId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function finalizeApprovedSale(int $saleId, int $approverId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $sale = $this->getSale($saleId);
            if (!$sale || $sale['status'] !== 'pendente_aprovacao') {
                throw new \RuntimeException('Venda inválida para finalização.');
            }

            $items = $this->getSaleItems($saleId);
            foreach ($items as $item) {
                if ($this->stock->getQuantity((int) $item['product_id']) < (int) $item['quantity']) {
                    throw new \RuntimeException('Estoque insuficiente para: ' . $item['product_name']);
                }
            }

            $pdo->prepare('UPDATE sales SET status = "concluida" WHERE id = ?')->execute([$saleId]);
            $pdo->prepare(
                'UPDATE discount_approvals SET status = "aprovado", approved_by = ?, resolved_at = NOW() WHERE sale_id = ?'
            )->execute([$approverId, $saleId]);

            foreach ($items as $item) {
                $this->stock->deductForSale(
                    (int) $item['product_id'],
                    (int) $item['quantity'],
                    (int) $sale['seller_id'],
                    $saleId
                );
            }

            $pdo->commit();
            audit_log('sale.approved', 'sale', $saleId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function rejectSale(int $saleId, int $approverId, ?string $notes): void
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'UPDATE discount_approvals SET status = "rejeitado", approved_by = ?, notes = ?, resolved_at = NOW() WHERE sale_id = ?'
        )->execute([$approverId, $notes, $saleId]);
        $pdo->prepare('UPDATE sales SET status = "estornada", notes = ? WHERE id = ?')
            ->execute(["Desconto rejeitado: {$notes}", $saleId]);
        audit_log('sale.rejected', 'sale', $saleId);
    }

    public function refund(int $saleId, int $userId, string $reason): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $sale = $this->getSale($saleId);
            if (!$sale || $sale['status'] !== 'concluida') {
                throw new \RuntimeException('Apenas vendas concluídas podem ser estornadas.');
            }

            $items = $this->getSaleItems($saleId);
            foreach ($items as $item) {
                $this->stock->restoreForRefund(
                    (int) $item['product_id'],
                    (int) $item['quantity'],
                    $userId,
                    $saleId
                );
            }

            $pdo->prepare(
                'INSERT INTO sale_refunds (sale_id, user_id, reason, refund_total) VALUES (?, ?, ?, ?)'
            )->execute([$saleId, $userId, $reason, $sale['total']]);

            $pdo->prepare('UPDATE sales SET status = "estornada" WHERE id = ?')->execute([$saleId]);
            $pdo->commit();
            audit_log('sale.refunded', 'sale', $saleId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function getSale(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT s.*, c.name AS customer_name, u.name AS seller_name
             FROM sales s
             JOIN customers c ON c.id = s.customer_id
             JOIN users u ON u.id = s.seller_id
             WHERE s.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getSaleItems(int $saleId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM sale_items WHERE sale_id = ?');
        $stmt->execute([$saleId]);
        return $stmt->fetchAll();
    }
}
