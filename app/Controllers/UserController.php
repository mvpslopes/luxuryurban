<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class UserController extends Controller
{
    public function index(): void
    {
        $users = Database::connection()->query(
            'SELECT id, name, username, email, role, active, created_at FROM users ORDER BY name'
        )->fetchAll();

        $this->view('users/index', compact('users'));
    }

    public function create(): void
    {
        $this->view('users/form', ['user' => null, 'title' => 'Novo usuário']);
    }

    public function store(): void
    {
        $this->requirePost();
        $errors = $this->validate([
            'name' => 'required',
            'username' => 'required',
            'password' => 'required',
            'role' => 'required',
        ]);

        $role = $_POST['role'] ?? '';
        if (!in_array($role, ['admin', 'vendedor'], true)) {
            $errors['role'] = 'Perfil inválido.';
        }

        if (strlen($_POST['password'] ?? '') < 8) {
            $errors['password'] = 'Senha deve ter no mínimo 8 caracteres.';
        }

        if ($errors) {
            $this->back($errors);
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, username, email, password_hash, role, active) VALUES (?, ?, ?, ?, ?, ?)'
        );

        try {
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['username']),
                trim($_POST['email'] ?? '') ?: null,
                password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                $role,
                isset($_POST['active']) ? 1 : 0,
            ]);
        } catch (\PDOException) {
            set_flash('error', 'Username já existe.');
            redirect('/usuarios');
        }

        audit_log('user.created', 'user', (int) $pdo->lastInsertId());
        set_flash('success', 'Usuário criado com sucesso.');
        redirect('/usuarios');
    }

    public function edit(string $id): void
    {
        $user = $this->find((int) $id);
        $this->view('users/form', ['user' => $user, 'title' => 'Editar usuário']);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        $user = $this->find((int) $id);

        $errors = $this->validate(['name' => 'required', 'username' => 'required', 'role' => 'required']);
        $role = $_POST['role'] ?? '';
        if ($user['role'] !== 'root' && !in_array($role, ['admin', 'vendedor'], true)) {
            $errors['role'] = 'Perfil inválido.';
        }

        if ($errors) {
            $this->back($errors);
        }

        $pdo = Database::connection();
        $password = $_POST['password'] ?? '';

        if ($password !== '') {
            if (strlen($password) < 8) {
                set_flash('error', 'Senha deve ter no mínimo 8 caracteres.');
                redirect('/usuarios');
            }
            $pdo->prepare(
                'UPDATE users SET name=?, username=?, email=?, password_hash=?, role=?, active=? WHERE id=?'
            )->execute([
                trim($_POST['name']), trim($_POST['username']), trim($_POST['email'] ?? '') ?: null,
                password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                $user['role'] === 'root' ? 'root' : $role,
                isset($_POST['active']) ? 1 : 0, $id,
            ]);
        } else {
            $pdo->prepare(
                'UPDATE users SET name=?, username=?, email=?, role=?, active=? WHERE id=?'
            )->execute([
                trim($_POST['name']), trim($_POST['username']), trim($_POST['email'] ?? '') ?: null,
                $user['role'] === 'root' ? 'root' : $role,
                isset($_POST['active']) ? 1 : 0, $id,
            ]);
        }

        audit_log('user.updated', 'user', (int) $id);
        set_flash('success', 'Usuário atualizado.');
        redirect('/usuarios');
    }

    private function find(int $id): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            http_response_code(404);
            exit('Usuário não encontrado.');
        }
        return $user;
    }
}
