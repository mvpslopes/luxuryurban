<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class PaymentMethodController extends Controller
{
    public function index(): void
    {
        $methods = Database::connection()->query(
            'SELECT pm.*, (SELECT COUNT(*) FROM sales s WHERE s.payment_method_id = pm.id) AS sales_count
             FROM payment_methods pm ORDER BY pm.sort_order, pm.name'
        )->fetchAll();

        $this->view('payment_methods/index', compact('methods'));
    }

    public function create(): void
    {
        $this->view('payment_methods/form', ['method' => null, 'title' => 'Nova forma de pagamento']);
    }

    public function store(): void
    {
        $this->requirePost();
        $errors = $this->validate(['name' => 'required']);
        if ($errors) {
            $this->back($errors);
        }

        try {
            Database::connection()->prepare(
                'INSERT INTO payment_methods (name, active, sort_order) VALUES (?, ?, ?)'
            )->execute([
                trim($_POST['name']),
                isset($_POST['active']) ? 1 : 0,
                (int) ($_POST['sort_order'] ?? 0),
            ]);
        } catch (\PDOException) {
            set_flash('error', 'Forma de pagamento já existe.');
            redirect('/formas-pagamento/novo');
        }

        set_flash('success', 'Forma de pagamento criada.');
        redirect('/formas-pagamento');
    }

    public function edit(string $id): void
    {
        $method = $this->find((int) $id);
        $this->view('payment_methods/form', ['method' => $method, 'title' => 'Editar forma de pagamento']);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        $this->find((int) $id);
        $errors = $this->validate(['name' => 'required']);
        if ($errors) {
            $this->back($errors);
        }

        Database::connection()->prepare(
            'UPDATE payment_methods SET name=?, active=?, sort_order=? WHERE id=?'
        )->execute([
            trim($_POST['name']),
            isset($_POST['active']) ? 1 : 0,
            (int) ($_POST['sort_order'] ?? 0),
            $id,
        ]);

        set_flash('success', 'Forma de pagamento atualizada.');
        redirect('/formas-pagamento');
    }

    private function find(int $id): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM payment_methods WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            exit('Forma de pagamento não encontrada.');
        }
        return $row;
    }
}
