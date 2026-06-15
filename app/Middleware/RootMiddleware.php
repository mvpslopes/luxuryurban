<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

class RootMiddleware
{
    public function handle(): void
    {
        if (!Auth::isRoot()) {
            http_response_code(403);
            exit('Acesso negado.');
        }
    }
}
