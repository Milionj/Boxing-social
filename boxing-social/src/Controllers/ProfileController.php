<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\AuthService;

final class ProfileController
{
    private User $users;    
    private AuthService $auth;

    public function __construct()
    {
        $this->users = new User();
        $this->auth = new AuthService();
    }

    private function requireAuth(Response $response): ?int
    {
        $id = $_SESSION['user']['id'] ?? null;
        if (!is_int($id)) {
            $response->redirect('/login');
            return null;
        }

        return $id;
    }

    public function show(Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            $response->errorPage(404, '404');
            return;
        }

        $errors = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['errors'], $_SESSION['success']);

        require dirname(__DIR__, 2) . '/templates/profile.php';
    }

    public function update(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        $username = trim((string) $request->input('username', ''));
        $email = strtolower(trim((string) $request->input('email', '')));
        $bio = trim((string) $request->input('bio', ''));

        $errors = [];

        if ($username === '' || strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = 'Le pseudo doit contenir entre 3 et 30 caracteres.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Le pseudo ne doit contenir que lettres, chiffres et underscore.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide.';
        }
        if ($this->users->existsByUsername($username, $userId)) {
            $errors[] = 'Ce pseudo est deja utilise.';
        }
        if ($this->users->existsByEmail($email, $userId)) {
            $errors[] = 'Cet email est deja utilise.';
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $response->redirect('/profile');
            return;
        }

        $this->users->updateProfile($userId, $username, $email, $bio);
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;
        $_SESSION['success'] = 'Profil mis a jour.';

        $response->redirect('/profile');
    }

    public function updatePassword(Request $request, Response $response): void
{
    $userId = $this->requireAuth($response);
    if ($userId === null) {
        return;
    }

    $currentPassword = (string) $request->input('current_password', '');
    $newPassword = (string) $request->input('new_password', '');
    $confirmPassword = (string) $request->input('confirm_password', '');

    $errors = [];

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $errors[] = 'Tous les champs mot de passe sont obligatoires.';
    }

    if (strlen($newPassword) < 8) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caracteres.';
    }

    if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $errors[] = 'Le nouveau mot de passe doit contenir une majuscule, une minuscule et un chiffre.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'La confirmation du nouveau mot de passe ne correspond pas.';
    }

    if (!$this->auth->verifyCurrentPassword($userId, $currentPassword)) {
        $errors[] = 'Mot de passe actuel incorrect.';
    }

    if ($errors !== []) {
        $_SESSION['errors_password'] = $errors;
        $response->redirect('/profile');
        return;
    }

    $this->auth->updatePassword($userId, $newPassword);
    $_SESSION['success_password'] = 'Mot de passe mis a jour avec succes.';
    $response->redirect('/profile');
}

}
