<?php

declare(strict_types=1);

/**
 * Ponto de entrada alternativo para hospedagem PHP compartilhada (Hostinger).
 * Use este arquivo se o document root for public_html/ e o restante
 * do projeto ficar FORA da pasta pública.
 *
 * Estrutura recomendada:
 *
 *   /home/usuario/
 *     luxuryurban/          ← app, config, vendor, .env (FORA do public_html)
 *       app/
 *       config/
 *       vendor/
 *       .env
 *     domains/luxuryurban.com.br/public_html/
 *       index.php           ← copie ESTE arquivo
 *       .htaccess           ← copie de public/.htaccess
 *       assets/             ← copie de public/assets/
 *       uploads/            ← copie de public/uploads/
 */

define('LUXURYURBAN_BASE', dirname(__DIR__) . '/luxuryurban');

require LUXURYURBAN_BASE . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(LUXURYURBAN_BASE);
if (file_exists(LUXURYURBAN_BASE . '/.env')) {
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

$router = require LUXURYURBAN_BASE . '/app/routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ?? '/');
