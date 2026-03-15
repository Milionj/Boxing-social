<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Database;
use PDO;

final class SearchService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function searchUsers(string $query, int $limit = 12): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, bio
             FROM users
             WHERE username LIKE :query
             ORDER BY username ASC
             LIMIT :lim'
        );
        $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function searchPosts(string $query, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id, p.title, p.content, p.image_path, p.media_type, p.media_size, p.created_at, u.username
             FROM posts p
             INNER JOIN users u ON u.id = p.user_id
             WHERE p.title LIKE :query_title
                OR p.content LIKE :query_content
                OR u.username LIKE :query_username
             ORDER BY p.created_at DESC
             LIMIT :lim'
        );
        $search = '%' . $query . '%';
        $stmt->bindValue(':query_title', $search, PDO::PARAM_STR);
        $stmt->bindValue(':query_content', $search, PDO::PARAM_STR);
        $stmt->bindValue(':query_username', $search, PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }
}
