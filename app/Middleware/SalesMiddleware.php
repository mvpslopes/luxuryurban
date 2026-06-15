<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

class SalesMiddleware
{
    public function handle(): void
    {
        if (!Auth::can('sales.create')) {
            http_response_code(403);
            exit('Acesso negado.');
        }
    }
}
