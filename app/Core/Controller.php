<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Método não permitido.');
        }
        verify_csrf();
    }

    protected function validate(array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = trim((string) ($_POST[$field] ?? ''));
            if (str_contains($rule, 'required') && $value === '') {
                $errors[$field] = 'Campo obrigatório.';
            }
        }
        return $errors;
    }

    protected function back(array $errors = [], array $old = []): never
    {
        $_SESSION['_errors'] = $errors;
        $_SESSION['_old'] = $old ?: $_POST;
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url(home_path())));
        exit;
    }

    protected function errors(): array
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        return $errors;
    }
}
