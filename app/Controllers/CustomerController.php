<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class CustomerController extends Controller
{
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $sql = 'SELECT c.*, u.name AS created_by_name,
                (SELECT COUNT(*) FROM sales s WHERE s.customer_id = c.id AND s.status = "concluida") AS sales_count
                FROM customers c JOIN users u ON u.id = c.created_by WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (c.name LIKE ? OR c.document LIKE ? OR c.phone LIKE ?)';
            $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
        }

        $sql .= ' ORDER BY c.name';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        $this->view('customers/index', ['customers' => $stmt->fetchAll(), 'search' => $search]);
    }

    public function create(): void
    {
        $this->view('customers/form', ['customer' => null, 'title' => 'Novo cliente']);
    }

    public function store(): void
    {
        $this->requirePost();
        $errors = $this->validate(['name' => 'required']);
        if ($errors) {
            $this->back($errors);
        }

        $pdo = Database::connection();
        try {
            $pdo->prepare(
                'INSERT INTO customers (name, document, email, phone, address_street, address_number,
                 address_neighborhood, address_city, address_state, address_zip, notes, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute($this->customerData());
        } catch (\PDOException) {
            set_flash('error', 'CPF/CNPJ já cadastrado.');
            redirect('/clientes');
        }

        set_flash('success', 'Cliente cadastrado.');
        redirect('/clientes');
    }

    public function edit(string $id): void
    {
        $customer = $this->find((int) $id);
        $sales = Database::connection()->prepare(
            'SELECT receipt_number, total, status, created_at FROM sales WHERE customer_id = ? ORDER BY created_at DESC LIMIT 20'
        );
        $sales->execute([$id]);

        $this->view('customers/form', [
            'customer' => $customer,
            'sales' => $sales->fetchAll(),
            'title' => 'Editar cliente',
        ]);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        $this->find((int) $id);
        $errors = $this->validate(['name' => 'required']);
        if ($errors) {
            $this->back($errors);
        }

        try {
            Database::connection()->prepare(
                'UPDATE customers SET name=?, document=?, email=?, phone=?, address_street=?, address_number=?,
                 address_neighborhood=?, address_city=?, address_state=?, address_zip=?, notes=? WHERE id=?'
            )->execute([...array_values($this->customerData()), $id]);
        } catch (\PDOException) {
            set_flash('error', 'CPF/CNPJ já cadastrado.');
            redirect('/clientes');
        }

        set_flash('success', 'Cliente atualizado.');
        redirect('/clientes');
    }

    public function searchApi(): void
    {
        $q = trim($_GET['q'] ?? '');
        $stmt = Database::connection()->prepare(
            'SELECT id, name, document, phone FROM customers
             WHERE name LIKE ? OR document LIKE ? ORDER BY name LIMIT 20'
        );
        $stmt->execute(["%{$q}%", "%{$q}%"]);
        $this->json($stmt->fetchAll());
    }

    private function find(int $id): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            exit('Cliente não encontrado.');
        }
        return $row;
    }

    private function customerData(): array
    {
        return [
            trim($_POST['name']),
            trim($_POST['document'] ?? '') ?: null,
            trim($_POST['email'] ?? '') ?: null,
            trim($_POST['phone'] ?? '') ?: null,
            trim($_POST['address_street'] ?? '') ?: null,
            trim($_POST['address_number'] ?? '') ?: null,
            trim($_POST['address_neighborhood'] ?? '') ?: null,
            trim($_POST['address_city'] ?? '') ?: null,
            strtoupper(trim($_POST['address_state'] ?? '')) ?: null,
            trim($_POST['address_zip'] ?? '') ?: null,
            trim($_POST['notes'] ?? '') ?: null,
            Auth::id(),
        ];
    }
}
