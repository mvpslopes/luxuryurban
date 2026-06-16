<?php

declare(strict_types=1);

/**
 * Entrada para hospedagem Hostinger (conteúdo de public_html/).
 * Tudo fica na mesma pasta: index.php, app/, config/, vendor/, assets/.
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
if (file_exists(__DIR__ . '/.env')) {
    $dotenv->load();
}

session_start();

$lifetime = (int) config('app')['session_lifetime'];
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $lifetime)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

$router = require __DIR__ . '/app/routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ?? '/');
