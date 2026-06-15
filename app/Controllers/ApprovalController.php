<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\SaleService;

class ApprovalController extends Controller
{
    private SaleService $sales;

    public function __construct()
    {
        $this->sales = new SaleService();
    }

    public function index(): void
    {
        $pending = Database::connection()->query(
            'SELECT da.*, s.receipt_number, s.total, s.discount_amount, c.name AS customer_name,
                    u.name AS requested_by_name
             FROM discount_approvals da
             JOIN sales s ON s.id = da.sale_id
             JOIN customers c ON c.id = s.customer_id
             JOIN users u ON u.id = da.requested_by
             WHERE da.status = "pendente"
             ORDER BY da.created_at DESC'
        )->fetchAll();

        $this->view('approvals/index', compact('pending'));
    }

    public function approve(string $id): void
    {
        $this->requirePost();

        $stmt = Database::connection()->prepare(
            'SELECT sale_id FROM discount_approvals WHERE id = ? AND status = "pendente"'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            set_flash('error', 'Aprovação não encontrada.');
            redirect('/aprovacoes');
        }

        try {
            $this->sales->finalizeApprovedSale((int) $row['sale_id'], Auth::id());
            set_flash('success', 'Desconto aprovado e venda concluída.');
        } catch (\Throwable $e) {
            set_flash('error', $e->getMessage());
        }

        redirect('/aprovacoes');
    }

    public function reject(string $id): void
    {
        $this->requirePost();
        $notes = trim($_POST['notes'] ?? 'Rejeitado pelo administrador.');

        $stmt = Database::connection()->prepare(
            'SELECT sale_id FROM discount_approvals WHERE id = ? AND status = "pendente"'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            set_flash('error', 'Aprovação não encontrada.');
            redirect('/aprovacoes');
        }

        $this->sales->rejectSale((int) $row['sale_id'], Auth::id(), $notes);
        set_flash('success', 'Desconto rejeitado.');
        redirect('/aprovacoes');
    }
}
