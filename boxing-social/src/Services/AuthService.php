<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Database;
use PDO;

final class AuthService
{

/**
     * Connexion PDO réutilisée pour toutes les opérations d'auth.
     */
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function usernameExists(string $username): bool {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        return (bool) $stmt->fetchColumn();
    }

    public function emailExists(string $email): bool {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :e LIMIT 1');
        $stmt->execute(['e' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function register(string $username, string $email, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password_hash) VALUES (:u, :e, :p)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'u' => $username,
            'e' => $email,
            'p' => $hash,
        ]);
    }

    /**
     * Tentative de connexion
     */
    public function attemptLogin(string $email, string $password): bool
    {
        $stmt = $this->pdo->prepare("SELECT id, username, email, password_hash, role FROM users WHERE email = :e LIMIT 1");
        $stmt->execute(['e' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        return true;
    }

    public function verifyCurrentPassword(int $userId, string $currentPassword): bool
{
    $stmt = $this->pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $hash = $stmt->fetchColumn();

    if (!$hash) {
        return false;
    }

    return password_verify($currentPassword, (string) $hash);
}

public function updatePassword(int $userId, string $newPassword): bool
{
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $this->pdo->prepare(
        'UPDATE users SET password_hash = :hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
    );

    return $stmt->execute([
        'hash' => $newHash,
        'id' => $userId,
    ]);
}


    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $param = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $param['path'], $param['domain'], $param['secure'], $param['httponly']);
        }
        session_destroy();
    }
}
