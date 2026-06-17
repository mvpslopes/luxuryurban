<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND active = 1 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => strtolower(trim((string) $user['role'])),
        ];

        audit_log('login', 'user', (int) $user['id']);
        return true;
    }

    public static function logout(): void
    {
        audit_log('logout');
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function role(): ?string
    {
        $role = self::user()['role'] ?? null;
        return $role !== null ? strtolower(trim((string) $role)) : null;
    }

    public static function isRoot(): bool
    {
        return self::role() === 'root';
    }

    public static function isAdmin(): bool
    {
        return in_array(self::role(), ['root', 'admin'], true);
    }

    public static function isVendedor(): bool
    {
        return self::role() === 'vendedor';
    }

    public static function can(string $permission): bool
    {
        $role = self::role();
        if (!$role) {
            return false;
        }

        $matrix = [
            'users.manage' => ['root'],
            'products.manage' => ['root', 'admin'],
            'categories.manage' => ['root', 'admin'],
            'payment_methods.manage' => ['root', 'admin'],
            'stock.manage' => ['root', 'admin'],
            'customers.manage' => ['root', 'admin', 'vendedor'],
            'sales.create' => ['root', 'admin', 'vendedor'],
            'sales.refund' => ['root', 'admin', 'vendedor'],
            'discounts.approve' => ['root', 'admin'],
        ];

        return in_array($role, $matrix[$permission] ?? [], true);
    }
}
