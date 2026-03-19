<?php
declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use PDO;

final class Comment
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function create(int $postId, int $userId, string $content): ?array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)'
        );

        $created = $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => $content,
        ]);

        if (!$created) {
            return null;
        }

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function deleteByOwner(int $commentId, int $ownerId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM comments WHERE id = :id AND user_id = :owner_id'
        );

        $stmt->execute([
            'id' => $commentId,
            'owner_id' => $ownerId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function findById(int $commentId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $commentId]);
        $comment = $stmt->fetch();

        return $comment ?: null;
    }

    public function byPostId(int $postId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.post_id = :post_id
             ORDER BY c.created_at DESC'
        );
        $stmt->execute(['post_id' => $postId]);

        return $stmt->fetchAll() ?: [];
    }

    public function countByPostId(int $postId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM comments WHERE post_id = :post_id');
        $stmt->execute(['post_id' => $postId]);

        return (int) $stmt->fetchColumn();
    }

    public function latestForAdmin(int $limit = 150): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             ORDER BY c.created_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function searchForAdmin(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        $params = [];
        $where = $this->adminCommentsWhere($filters, $params);

        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username, p.title AS post_title
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             INNER JOIN posts p ON p.id = c.post_id
             ' . $where . '
             ORDER BY c.created_at DESC, c.id DESC
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
        $where = $this->adminCommentsWhere($filters, $params);

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             INNER JOIN posts p ON p.id = c.post_id
             ' . $where
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function deleteByAdmin(int $commentId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM comments WHERE id = :id');

        return $stmt->execute(['id' => $commentId]);
    }

    private function adminCommentsWhere(array $filters, array &$params): string
    {
        $clauses = ['WHERE 1=1'];
        $query = trim((string) ($filters['query'] ?? ''));
        $postId = (int) ($filters['post_id'] ?? 0);

        if ($query !== '') {
            $params['query_content_like'] = '%' . $query . '%';
            $params['query_username_like'] = '%' . $query . '%';
            $params['query_post_title_like'] = '%' . $query . '%';
            $params['query_id_like'] = '%' . $query . '%';
            $clauses[] = 'AND (
                c.content LIKE :query_content_like
                OR u.username LIKE :query_username_like
                OR COALESCE(p.title, \'\') LIKE :query_post_title_like
                OR CAST(c.id AS CHAR) LIKE :query_id_like
            )';
        }

        if ($postId > 0) {
            $params['post_id'] = $postId;
            $clauses[] = 'AND c.post_id = :post_id';
        }

        return implode("\n", $clauses);
    }
}
