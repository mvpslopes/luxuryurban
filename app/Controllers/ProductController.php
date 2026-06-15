<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\StockService;

class ProductController extends Controller
{
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $categoryId = $_GET['category'] ?? '';

        $sql = 'SELECT p.*, c.name AS category_name, COALESCE(s.quantity, 0) AS stock_qty
                FROM products p
                JOIN product_categories c ON c.id = p.category_id
                LEFT JOIN stock s ON s.product_id = p.id WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($categoryId !== '') {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        $sql .= ' ORDER BY p.name';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        $categories = Database::connection()->query(
            'SELECT * FROM product_categories ORDER BY sort_order, name'
        )->fetchAll();

        $this->view('products/index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function create(): void
    {
        if (!Auth::can('products.manage')) {
            http_response_code(403);
            exit('Acesso negado.');
        }

        $categories = $this->activeCategories();
        $this->view('products/form', ['product' => null, 'categories' => $categories, 'images' => [], 'title' => 'Novo produto']);
    }

    public function store(): void
    {
        if (!Auth::can('products.manage')) {
            http_response_code(403);
            exit('Acesso negado.');
        }

        $this->requirePost();
        $errors = $this->validate(['name' => 'required', 'price' => 'required', 'category_id' => 'required']);
        if ($errors) {
            $this->back($errors);
        }

        $pdo = Database::connection();
        $sku = trim($_POST['sku'] ?? '') ?: $this->generateSku();
        $initialStock = max(0, (int) ($_POST['initial_stock'] ?? 0));

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO products (sku, name, description, category_id, price, cost_price, min_stock, active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $sku,
                trim($_POST['name']),
                trim($_POST['description'] ?? '') ?: null,
                (int) $_POST['category_id'],
                (float) str_replace(',', '.', $_POST['price']),
                $_POST['cost_price'] !== '' ? (float) str_replace(',', '.', $_POST['cost_price']) : null,
                max(0, (int) ($_POST['min_stock'] ?? 5)),
                isset($_POST['active']) ? 1 : 0,
            ]);

            $productId = (int) $pdo->lastInsertId();
            (new StockService())->initialize($productId, $initialStock, (int) Auth::id(), 'Estoque inicial');

            $this->handleUploads($productId);
            $pdo->commit();

            set_flash('success', 'Produto criado.');
            redirect('/produtos');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            set_flash('error', $e->getMessage());
            redirect('/produtos/novo');
        }
    }

    public function edit(string $id): void
    {
        if (!Auth::can('products.manage')) {
            http_response_code(403);
            exit('Acesso negado.');
        }

        $product = $this->find((int) $id);
        $images = Database::connection()->prepare(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id'
        );
        $images->execute([$id]);
        $images = $images->fetchAll();

        $this->view('products/form', [
            'product' => $product,
            'categories' => $this->allCategories(),
            'images' => $images,
            'title' => 'Editar produto',
        ]);
    }

    public function update(string $id): void
    {
        if (!Auth::can('products.manage')) {
            http_response_code(403);
            exit('Acesso negado.');
        }

        $this->requirePost();
        $this->find((int) $id);
        $errors = $this->validate(['name' => 'required', 'price' => 'required', 'category_id' => 'required']);
        if ($errors) {
            $this->back($errors);
        }

        Database::connection()->prepare(
            'UPDATE products SET sku=?, name=?, description=?, category_id=?, price=?, cost_price=?, min_stock=?, active=? WHERE id=?'
        )->execute([
            trim($_POST['sku']),
            trim($_POST['name']),
            trim($_POST['description'] ?? '') ?: null,
            (int) $_POST['category_id'],
            (float) str_replace(',', '.', $_POST['price']),
            $_POST['cost_price'] !== '' ? (float) str_replace(',', '.', $_POST['cost_price']) : null,
            max(0, (int) ($_POST['min_stock'] ?? 5)),
            isset($_POST['active']) ? 1 : 0,
            $id,
        ]);

        $this->handleUploads((int) $id);
        set_flash('success', 'Produto atualizado.');
        redirect('/produtos');
    }

    public function searchApi(): void
    {
        $q = trim($_GET['q'] ?? '');
        $stmt = Database::connection()->prepare(
            'SELECT p.id, p.name, p.sku, p.price, COALESCE(s.quantity, 0) AS stock
             FROM products p
             LEFT JOIN stock s ON s.product_id = p.id
             WHERE p.active = 1 AND (p.name LIKE ? OR p.sku LIKE ?)
             ORDER BY p.name LIMIT 20'
        );
        $stmt->execute(["%{$q}%", "%{$q}%"]);
        $this->json($stmt->fetchAll());
    }

    private function find(int $id): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.*, COALESCE(s.quantity,0) AS stock_qty FROM products p
             LEFT JOIN stock s ON s.product_id = p.id WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            exit('Produto não encontrado.');
        }
        return $row;
    }

    private function activeCategories(): array
    {
        return Database::connection()->query(
            'SELECT * FROM product_categories WHERE active = 1 ORDER BY sort_order, name'
        )->fetchAll();
    }

    private function allCategories(): array
    {
        return Database::connection()->query(
            'SELECT * FROM product_categories ORDER BY sort_order, name'
        )->fetchAll();
    }

    private function generateSku(): string
    {
        return 'LU-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    private function handleUploads(int $productId): void
    {
        if (empty($_FILES['photos']['name'][0])) {
            return;
        }

        $pdo = Database::connection();
        $count = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ?');
        $count->execute([$productId]);
        $existing = (int) $count->fetchColumn();

        $uploadDir = config('app')['upload_path'];
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $files = $_FILES['photos'];

        foreach ($files['name'] as $i => $name) {
            if ($existing >= 5) {
                break;
            }
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmp = $files['tmp_name'][$i];
            $mime = mime_content_type($tmp);
            if (!in_array($mime, $allowed, true) || $files['size'][$i] > config('app')['max_upload_size']) {
                continue;
            }

            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };

            $filename = $productId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            move_uploaded_file($tmp, $uploadDir . '/' . $filename);

            $isPrimary = $existing === 0 ? 1 : 0;
            $pdo->prepare(
                'INSERT INTO product_images (product_id, filename, is_primary, sort_order) VALUES (?, ?, ?, ?)'
            )->execute([$productId, $filename, $isPrimary, $existing]);
            $existing++;
        }
    }
}
