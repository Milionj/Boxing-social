<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Post;
use App\Models\User;
use App\Services\AuthService;

final class ProfileController
{
    private User $users;    
    private Post $posts;
    private AuthService $auth;

    public function __construct()
    {
        $this->users = new User();
        $this->posts = new Post();
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

    public function publicShow(Request $request, Response $response): void
    {
        $username = trim((string) $request->input('username', ''));
        if ($username === '') {
            $response->errorPage(404, '404');
            return;
        }

        $user = $this->users->findByUsername($username);
        if ($user === null) {
            $response->errorPage(404, '404');
            return;
        }

        $posts = $this->posts->latestPublicByUserId((int) $user['id']);

        require dirname(__DIR__, 2) . '/templates/public-profile.php';
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

// modification de la photo de profil
    public function updateAvatar(Response $response): void
{
    $userId = $this->requireAuth($response);
    if ($userId === null) {
        return;
    }

    if (!isset($_FILES['avatar']) || !is_array($_FILES['avatar'])) {
        $_SESSION['errors_avatar'] = ['Aucun fichier recu.'];
        $response->redirect('/profile');
        return;
    }

    $file = $_FILES['avatar'];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $_SESSION['errors_avatar'] = ['Erreur upload fichier.'];
        $response->redirect('/profile');
        return;
    }

    $maxSize = 2 * 1024 * 1024; // 2MB
    if (($file['size'] ?? 0) > $maxSize) {
        $_SESSION['errors_avatar'] = ['Fichier trop volumineux (max 2MB).'];
        $response->redirect('/profile');
        return;
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $mime = mime_content_type($tmpPath) ?: '';

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedMimes[$mime])) {
        $_SESSION['errors_avatar'] = ['Format non autorise. Utilise JPG, PNG ou WEBP.'];
        $response->redirect('/profile');
        return;
    }

    $extension = $allowedMimes[$mime];
    $newName = 'avatar_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/avatars';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        $_SESSION['errors_avatar'] = ['Impossible de creer le dossier de stockage.'];
        $response->redirect('/profile');
        return;
    }

    $targetPath = $uploadDir . '/' . $newName;
    if (!move_uploaded_file($tmpPath, $targetPath)) {
        $_SESSION['errors_avatar'] = ['Impossible de deplacer le fichier.'];
        $response->redirect('/profile');
        return;
    }

    $publicPath = '/uploads/avatars/' . $newName;
    $this->users->updateAvatarPath($userId, $publicPath);

    $_SESSION['success_avatar'] = 'Photo de profil mise a jour.';
    $response->redirect('/profile');
}

}
