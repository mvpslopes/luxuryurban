<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class CategoryController extends Controller
{
    public function index(): void
    {
        $categories = Database::connection()->query(
            'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS products_count
             FROM product_categories c ORDER BY c.sort_order, c.name'
        )->fetchAll();

        $this->view('categories/index', compact('categories'));
    }

    public function create(): void
    {
        $this->view('categories/form', ['category' => null, 'title' => 'Nova categoria']);
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
                'INSERT INTO product_categories (name, active, sort_order) VALUES (?, ?, ?)'
            )->execute([
                trim($_POST['name']),
                isset($_POST['active']) ? 1 : 0,
                (int) ($_POST['sort_order'] ?? 0),
            ]);
        } catch (\PDOException) {
            set_flash('error', 'Categoria já existe.');
            redirect('/categorias');
        }

        set_flash('success', 'Categoria criada.');
        redirect('/categorias');
    }

    public function storeApi(): void
    {
        if (!Auth::can('categories.manage')) {
            $this->json(['error' => 'Acesso negado.'], 403);
        }

        $this->requirePost();
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->json(['error' => 'Informe o nome da categoria.'], 422);
        }

        $pdo = Database::connection();
        try {
            $pdo->prepare(
                'INSERT INTO product_categories (name, active, sort_order) VALUES (?, 1, 0)'
            )->execute([$name]);

            $this->json([
                'id' => (int) $pdo->lastInsertId(),
                'name' => $name,
            ]);
        } catch (\PDOException) {
            $this->json(['error' => 'Categoria já existe.'], 409);
        }
    }

    public function edit(string $id): void
    {
        $category = $this->find((int) $id);
        $this->view('categories/form', ['category' => $category, 'title' => 'Editar categoria']);
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
            'UPDATE product_categories SET name=?, active=?, sort_order=? WHERE id=?'
        )->execute([
            trim($_POST['name']),
            isset($_POST['active']) ? 1 : 0,
            (int) ($_POST['sort_order'] ?? 0),
            $id,
        ]);

        set_flash('success', 'Categoria atualizada.');
        redirect('/categorias');
    }

    private function find(int $id): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM product_categories WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            exit('Categoria não encontrada.');
        }
        return $row;
    }
}
