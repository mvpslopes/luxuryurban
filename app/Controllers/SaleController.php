<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\PdfService;
use App\Services\SaleService;

class SaleController extends Controller
{
    private SaleService $sales;

    public function __construct()
    {
        $this->sales = new SaleService();
    }

    public function index(): void
    {
        $status = $_GET['status'] ?? '';
        $sql = 'SELECT s.*,
                       c.name AS customer_name,
                       u.name AS seller_name
                FROM sales s
                JOIN customers c ON c.id = s.customer_id
                JOIN users u ON u.id = s.seller_id WHERE 1=1';
        $params = [];

        if (!Auth::isAdmin()) {
            $sql .= ' AND s.seller_id = ?';
            $params[] = Auth::id();
        }
        if ($status !== '') {
            $sql .= ' AND s.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY s.created_at DESC LIMIT 100';
        try {
            $stmt = Database::connection()->prepare($sql);
            $stmt->execute($params);
            $this->view('sales/index', ['sales' => $stmt->fetchAll(), 'status' => $status]);
        } catch (\Throwable $e) {
            $debug = (bool) (config('app')['debug'] ?? false);
            set_flash('error', $debug ? ('Erro no carregamento de vendas: ' . $e->getMessage()) : 'Erro ao carregar pedidos no momento.');
            redirect('/vendas');
        }
    }

    public function create(): void
    {
        $paymentMethods = Database::connection()->query(
            'SELECT * FROM payment_methods WHERE active = 1 ORDER BY sort_order, name'
        )->fetchAll();

        $customers = Database::connection()->query(
            'SELECT id, name, document, phone FROM customers ORDER BY name LIMIT 300'
        )->fetchAll();

        $products = Database::connection()->query(
            'SELECT p.id, p.name, p.sku, p.price, COALESCE(s.quantity, 0) AS stock
             FROM products p
             LEFT JOIN stock s ON s.product_id = p.id
             WHERE p.active = 1
             ORDER BY p.name'
        )->fetchAll();

        $this->view('sales/create', compact('paymentMethods', 'customers', 'products'));
    }

    public function store(): void
    {
        $this->requirePost();

        $items = json_decode($_POST['items_json'] ?? '[]', true);
        if (empty($items)) {
            set_flash('error', 'Adicione pelo menos um produto.');
            redirect('/vendas/nova');
        }

        $paymentMethodId = (int) ($_POST['payment_method_id'] ?? 0);
        $pm = Database::connection()->prepare('SELECT * FROM payment_methods WHERE id = ? AND active = 1');
        $pm->execute([$paymentMethodId]);
        $paymentMethod = $pm->fetch();

        if (!$paymentMethod) {
            set_flash('error', 'Selecione uma forma de pagamento válida.');
            redirect('/vendas/nova');
        }

        $customerId = (int) ($_POST['customer_id'] ?? 0);
        if (!$customerId) {
            set_flash('error', 'Selecione um cliente.');
            redirect('/vendas/nova');
        }

        try {
            $saleId = $this->sales->createSale([
                'customer_id' => $customerId,
                'seller_id' => Auth::id(),
                'items' => $items,
                'discount_type' => $_POST['discount_type'] ?? 'percent',
                'discount_value' => (float) str_replace(',', '.', $_POST['discount_value'] ?? '0'),
                'payment_method_id' => $paymentMethodId,
                'payment_method_name' => $paymentMethod['name'],
                'notes' => trim($_POST['notes'] ?? '') ?: null,
            ]);

            $sale = $this->sales->getSale($saleId);
            if ($sale['status'] === 'pendente_aprovacao') {
                set_flash('success', 'Venda registrada e aguardando aprovação de desconto.');
            } else {
                set_flash('success', 'Venda concluída com sucesso.');
            }
            redirect("/vendas/{$saleId}");
        } catch (\Throwable $e) {
            set_flash('error', $e->getMessage());
            redirect('/vendas/nova');
        }
    }

    public function show(string $id): void
    {
        $sale = $this->sales->getSale((int) $id);
        if (!$sale) {
            http_response_code(404);
            exit('Venda não encontrada.');
        }

        if (!Auth::isAdmin() && (int) $sale['seller_id'] !== Auth::id()) {
            http_response_code(403);
            exit('Acesso negado.');
        }

        $items = $this->sales->getSaleItems((int) $id);
        $this->view('sales/show', compact('sale', 'items'));
    }

    public function pdf(string $id): void
    {
        $sale = $this->sales->getSale((int) $id);
        if (!$sale || $sale['status'] !== 'concluida') {
            http_response_code(404);
            exit('Recibo não disponível.');
        }

        if (!Auth::isAdmin() && (int) $sale['seller_id'] !== Auth::id()) {
            http_response_code(403);
            exit('Acesso negado.');
        }

        $items = $this->sales->getSaleItems((int) $id);
        $pdf = (new PdfService())->receipt($sale, $items);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="recibo-' . $sale['receipt_number'] . '.pdf"');
        echo $pdf;
        exit;
    }

    public function refundForm(string $id): void
    {
        $sale = $this->sales->getSale((int) $id);
        if (!$sale || $sale['status'] !== 'concluida') {
            set_flash('error', 'Venda não pode ser estornada.');
            redirect('/vendas');
        }

        $items = $this->sales->getSaleItems((int) $id);
        $this->view('sales/refund', compact('sale', 'items'));
    }

    public function refund(string $id): void
    {
        $this->requirePost();
        $reason = trim($_POST['reason'] ?? '');

        if ($reason === '') {
            set_flash('error', 'Informe o motivo do estorno.');
            redirect("/vendas/{$id}/estornar");
        }

        try {
            $this->sales->refund((int) $id, (int) Auth::id(), $reason);
            set_flash('success', 'Estorno realizado com sucesso.');
            redirect("/vendas/{$id}");
        } catch (\Throwable $e) {
            set_flash('error', $e->getMessage());
            redirect("/vendas/{$id}/estornar");
        }
    }
}
