<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

final class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    public function showRegister(Response $response): void
    {
        require dirname(__DIR__, 2) . '/templates/register.php';
    }

    public function register(Request $request, Response $response): void
    {
        $username = trim((string)$request->input('username', ''));
        $email = trim((string)$request->input('email', ''));
        $password = (string)$request->input('password', '');

        if ($username === '' || $email === '' || $password === '') {
            $response->html('Champs requis manquants', 422);
            return;
        }

        $ok = $this->auth->register($username, $email, $password);

        if (!$ok) {
            $response->html('Inscription impossible (pseudo/email déjà pris ?)', 400);
            return;
        }

        $response->redirect('/login');
    }

    public function showLogin(Response $response): void
    {
        require dirname(__DIR__, 2) . '/templates/login.php';
    }

    public function login(Request $request, Response $response): void
    {
        $email = trim((string)$request->input('email', ''));
        $password = (string)$request->input('password', '');

        if (!$this->auth->attemptLogin($email, $password)) {
            $response->html('Identifiants invalides', 401);
            return;
        }

        $response->redirect('/');
    }

    public function logout(Response $response): void
    {
        $this->auth->logout();
        $response->redirect('/login');
    }
}
