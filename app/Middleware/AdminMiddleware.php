<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

class AdminMiddleware
{
    public function handle(): void
    {
        if (!Auth::isAdmin()) {
            set_flash('error', 'Você não tem permissão para acessar esta página.');
            redirect(home_path());
        }
    }
}
