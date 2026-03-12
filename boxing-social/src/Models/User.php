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
            'SELECT id, username, email, bio, avatar_path, role, created_at FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, bio, avatar_path, role, created_at
             FROM users
             WHERE username = :username
             LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function searchByUsername(string $query, int $limit = 8): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, bio
             FROM users
             WHERE username LIKE :query
             ORDER BY username ASC
             LIMIT :lim'
        );
        $stmt->bindValue(':query', $query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
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

    public function updateAvatarPath(int $id, string $avatarPath): bool{
        $stmt = $this->pdo->prepare(
            'UPDATE users SET avatar_path = :avatar, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        return $stmt->execute([
            'avatar' => $avatarPath,
            'id' => $id,
        ]);
    }

    public function getAvatarPathById(int $id): ?string{
        $stmt = $this->pdo->prepare(
        'SELECT avatar_path FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $value = $stmt->fetchColumn();
        return $value !== false ? (string) $value : null;
    }

    public function latestUsers(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, role, is_active, created_at
             FROM users
             ORDER BY created_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function setActiveByAdmin(int $userId, bool $isActive): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET is_active = :is_active, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        return $stmt->execute([
            'is_active' => $isActive ? 1 : 0,
            'id' => $userId,
        ]);
    }
}
