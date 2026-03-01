<?php
declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use PDO;

final class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, bio, role, created_at FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function existsByUsername(string $username, int $exceptId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM users WHERE username = :u AND id <> :id LIMIT 1'
        );
        $stmt->execute(['u' => $username, 'id' => $exceptId]);
        return (bool) $stmt->fetchColumn();
    }

    public function existsByEmail(string $email, int $exceptId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM users WHERE email = :e AND id <> :id LIMIT 1'
        );
        $stmt->execute(['e' => $email, 'id' => $exceptId]);
        return (bool) $stmt->fetchColumn();
    }

    public function updateProfile(int $id, string $username, string $email, string $bio): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET username = :u, email = :e, bio = :b WHERE id = :id'
        );

        return $stmt->execute([
            'u' => $username,
            'e' => $email,
            'b' => $bio,
            'id' => $id,
        ]);
    }
}
