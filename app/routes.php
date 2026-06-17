<?php

declare(strict_types=1);

use App\Controllers\ApprovalController;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\CustomerController;
use App\Controllers\DashboardController;
use App\Controllers\PaymentMethodController;
use App\Controllers\ProductController;
use App\Controllers\SaleController;
use App\Controllers\StockController;
use App\Controllers\UserController;
use App\Core\Router;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RootMiddleware;
use App\Middleware\SalesMiddleware;

$auth = [AuthMiddleware::class];
$admin = [AuthMiddleware::class, AdminMiddleware::class];
$root = [AuthMiddleware::class, RootMiddleware::class];
$sales = [AuthMiddleware::class, SalesMiddleware::class];
$guest = [GuestMiddleware::class];

/** @var Router $router */
$router = new Router();

$router->get('/login', [AuthController::class, 'showLogin'], $guest);
$router->post('/login', [AuthController::class, 'login'], $guest);
$router->get('/logout', [AuthController::class, 'logout'], $auth);

$router->get('/', [AuthController::class, 'home'], $auth);
$router->get('/dashboard', [DashboardController::class, 'index'], $admin);

$router->get('/usuarios', [UserController::class, 'index'], $root);
$router->get('/usuarios/novo', [UserController::class, 'create'], $root);
$router->post('/usuarios', [UserController::class, 'store'], $root);
$router->get('/usuarios/{id}/editar', [UserController::class, 'edit'], $root);
$router->post('/usuarios/{id}', [UserController::class, 'update'], $root);

$router->get('/categorias', [CategoryController::class, 'index'], $admin);
$router->get('/categorias/novo', [CategoryController::class, 'create'], $admin);
$router->post('/categorias', [CategoryController::class, 'store'], $admin);
$router->get('/categorias/{id}/editar', [CategoryController::class, 'edit'], $admin);
$router->post('/categorias/{id}', [CategoryController::class, 'update'], $admin);
$router->post('/api/categorias', [CategoryController::class, 'storeApi'], $admin);

$router->get('/formas-pagamento', [PaymentMethodController::class, 'index'], $admin);
$router->get('/formas-pagamento/novo', [PaymentMethodController::class, 'create'], $admin);
$router->post('/formas-pagamento', [PaymentMethodController::class, 'store'], $admin);
$router->get('/formas-pagamento/{id}/editar', [PaymentMethodController::class, 'edit'], $admin);
$router->post('/formas-pagamento/{id}', [PaymentMethodController::class, 'update'], $admin);

$router->get('/produtos', [ProductController::class, 'index'], $auth);
$router->get('/produtos/novo', [ProductController::class, 'create'], $admin);
$router->post('/produtos', [ProductController::class, 'store'], $admin);
$router->get('/produtos/{id}/editar', [ProductController::class, 'edit'], $admin);
$router->post('/produtos/{id}', [ProductController::class, 'update'], $admin);
$router->get('/api/produtos', [ProductController::class, 'searchApi'], $sales);

$router->get('/estoque', [StockController::class, 'index'], $admin);
$router->get('/estoque/movimentacao', [StockController::class, 'movement'], $admin);
$router->post('/estoque/movimentacao', [StockController::class, 'storeMovement'], $admin);
$router->get('/estoque/historico', [StockController::class, 'history'], $admin);

$router->get('/clientes', [CustomerController::class, 'index'], $auth);
$router->get('/clientes/novo', [CustomerController::class, 'create'], $sales);
$router->post('/clientes', [CustomerController::class, 'store'], $sales);
$router->get('/clientes/{id}/editar', [CustomerController::class, 'edit'], $sales);
$router->post('/clientes/{id}', [CustomerController::class, 'update'], $sales);
$router->get('/api/clientes', [CustomerController::class, 'searchApi'], $sales);
$router->post('/api/clientes', [CustomerController::class, 'storeApi'], $sales);

$router->get('/vendas', [SaleController::class, 'index'], $auth);
$router->get('/vendas/nova', [SaleController::class, 'create'], $sales);
$router->post('/vendas', [SaleController::class, 'store'], $sales);
$router->get('/vendas/{id}', [SaleController::class, 'show'], $auth);
$router->get('/vendas/{id}/pdf', [SaleController::class, 'pdf'], $auth);
$router->get('/vendas/{id}/estornar', [SaleController::class, 'refundForm'], $sales);
$router->post('/vendas/{id}/estornar', [SaleController::class, 'refund'], $sales);

$router->get('/aprovacoes', [ApprovalController::class, 'index'], $admin);
$router->post('/aprovacoes/{id}/aprovar', [ApprovalController::class, 'approve'], $admin);
$router->post('/aprovacoes/{id}/rejeitar', [ApprovalController::class, 'reject'], $admin);

return $router;
