<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', [], 'auth');
    }

    public function login(): void
    {
        $this->requirePost();
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            set_flash('error', 'Informe usuário e senha.');
            redirect('/login');
        }

        if (!Auth::attempt($username, $password)) {
            set_flash('error', 'Credenciais inválidas.');
            redirect('/login');
        }

        redirect(home_path());
    }

    public function home(): void
    {
        redirect(home_path());
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('/login');
    }
}
