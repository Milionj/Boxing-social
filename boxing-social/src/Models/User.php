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

    public function searchForAdmin(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        $params = [];
        $where = $this->adminUsersWhere($filters, $params);

        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.username, u.email, u.role, u.is_active, u.created_at, u.bio
             FROM users u
             ' . $where . '
             ORDER BY u.created_at DESC, u.id DESC
             LIMIT :lim OFFSET :off'
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function countForAdmin(array $filters = []): int
    {
        $params = [];
        $where = $this->adminUsersWhere($filters, $params);

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM users u
             ' . $where
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
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

    private function adminUsersWhere(array $filters, array &$params): string
    {
        $clauses = ['WHERE 1=1'];
        $query = trim((string) ($filters['query'] ?? ''));
        $role = (string) ($filters['role'] ?? '');
        $status = (string) ($filters['status'] ?? '');

        if ($query !== '') {
            $params['query_username_like'] = '%' . $query . '%';
            $params['query_email_like'] = '%' . $query . '%';
            $params['query_id_like'] = '%' . $query . '%';
            $clauses[] = 'AND (
                u.username LIKE :query_username_like
                OR u.email LIKE :query_email_like
                OR CAST(u.id AS CHAR) LIKE :query_id_like
            )';
        }

        if (in_array($role, ['user', 'admin'], true)) {
            $params['role'] = $role;
            $clauses[] = 'AND u.role = :role';
        }

        if ($status === 'active' || $status === 'disabled') {
            $params['is_active'] = $status === 'active' ? 1 : 0;
            $clauses[] = 'AND u.is_active = :is_active';
        }

        return implode("\n", $clauses);
    }
}
