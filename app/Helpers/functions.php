<?php

declare(strict_types=1);

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return $value;
}

function config(string $file): array
{
    static $cache = [];
    if (!isset($cache[$file])) {
        $cache[$file] = require dirname(__DIR__, 2) . "/config/{$file}.php";
    }
    return $cache[$file];
}

function base_path(string $path = ''): string
{
    return dirname(__DIR__, 2) . ($path ? '/' . ltrim($path, '/') : '');
}

function public_path(string $path = ''): string
{
    return base_path('public') . ($path ? '/' . ltrim($path, '/') : '');
}

function storage_path(string $path = ''): string
{
    return base_path('storage') . ($path ? '/' . ltrim($path, '/') : '');
}

function url(string $path = ''): string
{
    $base = rtrim(config('app')['url'], '/');
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    $candidates = [
        base_path('public/' . $path),
        base_path($path),
    ];

    $version = time();
    foreach ($candidates as $file) {
        if (is_file($file)) {
            $version = filemtime($file);
            break;
        }
    }

    return url($path) . '?v=' . $version;
}

function home_path(): string
{
    return \App\Core\Auth::isVendedor() ? '/vendas/nova' : '/dashboard';
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key): ?string
{
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function set_flash(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Token CSRF inválido.');
    }
}

function money(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function format_date(?string $datetime): string
{
    if (!$datetime) {
        return '-';
    }
    return date('d/m/Y H:i', strtotime($datetime));
}

function role_label(string $role): string
{
    return match ($role) {
        'root' => 'Root',
        'admin' => 'Admin',
        'vendedor' => 'Vendedor',
        default => $role,
    };
}

function sale_status_label(string $status): string
{
    return match ($status) {
        'concluida' => 'Concluída',
        'pendente_aprovacao' => 'Pendente',
        'estornada' => 'Estornada',
        default => $status,
    };
}

function sale_status_class(string $status): string
{
    return match ($status) {
        'concluida' => 'badge-success',
        'pendente_aprovacao' => 'badge-warning',
        'estornada' => 'badge-danger',
        default => 'badge-neutral',
    };
}

function audit_log(string $action, ?string $entityType = null, ?int $entityId = null, ?array $details = null): void
{
    try {
        $pdo = \App\Core\Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $userId = \App\Core\Auth::id();
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (\Throwable) {
        // Silencioso — log não deve quebrar fluxo principal
    }
}

function can(string $permission): bool
{
    return \App\Core\Auth::can($permission);
}

function is_active_nav(string $path): string
{
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = parse_url(url('/'), PHP_URL_PATH) ?: '';
    $current = str_replace($base, '', $current);
    $current = '/' . trim($current, '/');
    $path = '/' . trim($path, '/');

    if ($path === '/dashboard' && ($current === '/' || $current === '/dashboard')) {
        return 'active';
    }

    return str_starts_with($current, $path) ? 'active' : '';
}
