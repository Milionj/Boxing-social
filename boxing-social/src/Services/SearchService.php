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

    public function countUsers(string $query): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM users
             WHERE username LIKE :query
                OR bio LIKE :query_bio'
        );
        $stmt->execute([
            'query' => '%' . $query . '%',
            'query_bio' => '%' . $query . '%',
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function searchUsers(string $query, int $limit = 12, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, bio
             FROM users
             WHERE username LIKE :query
                OR bio LIKE :query_bio
             ORDER BY CASE
                        WHEN username = :query_exact THEN 0
                        WHEN username LIKE :query_starts THEN 1
                        WHEN bio LIKE :query_starts_bio THEN 2
                        ELSE 3
                      END,
                      username ASC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':query_bio', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':query_exact', $query, PDO::PARAM_STR);
        $stmt->bindValue(':query_starts', $query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':query_starts_bio', $query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function countPosts(string $query, string $postType = 'all'): int
    {
        $params = [];
        $where = $this->postSearchWhere($query, $postType, $params);
        unset(
            $params['query_title_starts'],
            $params['query_content_starts'],
            $params['query_username_starts'],
            $params['query_location_starts']
        );

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM posts p
             INNER JOIN users u ON u.id = p.user_id
             ' . $where
        );

        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function searchPosts(string $query, string $postType = 'all', int $limit = 20, int $offset = 0): array
    {
        $params = [];
        $where = $this->postSearchWhere($query, $postType, $params);

        $stmt = $this->pdo->prepare(
            'SELECT p.id,
                    p.title,
                    p.content,
                    p.image_path,
                    p.media_type,
                    p.media_size,
                    p.location,
                    p.post_type,
                    p.scheduled_at,
                    p.created_at,
                    u.username
             FROM posts p
             INNER JOIN users u ON u.id = p.user_id
             ' . $where . '
             ORDER BY CASE
                        WHEN p.title LIKE :query_title_starts THEN 0
                        WHEN u.username LIKE :query_username_starts THEN 1
                        WHEN p.location LIKE :query_location_starts THEN 2
                        WHEN p.content LIKE :query_content_starts THEN 3
                        ELSE 4
                      END,
                      p.created_at DESC
             LIMIT :lim OFFSET :off'
        );

        foreach ($params as $name => $value) {
            $stmt->bindValue(':' . $name, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    private function postSearchWhere(string $query, string $postType, array &$params): string
    {
        $search = '%' . $query . '%';
        $params = [
            'query_title' => $search,
            'query_content' => $search,
            'query_username' => $search,
            'query_location' => $search,
            'query_title_starts' => $query . '%',
            'query_content_starts' => $query . '%',
            'query_username_starts' => $query . '%',
            'query_location_starts' => $query . '%',
        ];

        $conditions = [
            '(p.title LIKE :query_title
              OR p.content LIKE :query_content
              OR u.username LIKE :query_username
              OR p.location LIKE :query_location)',
        ];

        if (in_array($postType, ['publication', 'entrainement'], true)) {
            $conditions[] = 'p.post_type = :post_type';
            $params['post_type'] = $postType;
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }
}
