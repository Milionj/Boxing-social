<?php
declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use PDO;

final class Post
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function create(
        int $userId,
        string $postType,
        string $title,
        string $content,
        ?string $mediaPath,
        string $mediaType,
        string $mediaSize,
        ?string $location,
        string $visibility = 'public',
        ?string $scheduledAt = null
    ): bool {
        // On stocke maintenant le type du post pour distinguer :
        // - une publication simple
        // - une declaration de seance d entrainement
        $stmt = $this->pdo->prepare(
            'INSERT INTO posts (user_id, post_type, title, content, image_path, media_type, media_size, location, visibility, scheduled_at)
             VALUES (:user_id, :post_type, :title, :content, :image_path, :media_type, :media_size, :location, :visibility, :scheduled_at)'
        );

        return $stmt->execute([
            'user_id' => $userId,
            'post_type' => $postType,
            'title' => $title !== '' ? $title : null,
            'content' => $content,
            'image_path' => $mediaPath,
            'media_type' => $mediaType,
            'media_size' => $mediaSize,
            'location' => $location !== '' ? $location : null,
            'visibility' => $visibility,
            'scheduled_at' => $scheduledAt,
        ]);
    }

    public function latestFeed(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                p.id,
                p.user_id,
                p.post_type,
                p.title,
                p.content,
                p.image_path,
                p.media_type,
                p.media_size,
                p.location,
                p.scheduled_at,
                p.visibility,
                p.created_at,
                u.username
             FROM posts p
             INNER JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function feedCount(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM posts');
        return (int) $stmt->fetchColumn();
    }

    public function latestByUserId(int $userId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                p.id,
                p.user_id,
                p.post_type,
                p.title,
                p.content,
                p.image_path,
                p.media_type,
                p.media_size,
                p.location,
                p.scheduled_at,
                p.visibility,
                p.created_at,
                u.username
             FROM posts p
             INNER JOIN users u ON u.id = p.user_id
             WHERE p.user_id = :user_id
             ORDER BY p.created_at DESC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function countByUserId(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM posts
             WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, post_type, title, content, image_path, media_type, media_size, location, scheduled_at, visibility, created_at
             FROM posts
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();

        return $post ?: null;
    }

    public function findDetailedById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                p.id,
                p.user_id,
                p.post_type,
                p.title,
                p.content,
                p.image_path,
                p.media_type,
                p.media_size,
                p.location,
                p.scheduled_at,
                p.visibility,
                p.created_at,
                u.username
             FROM posts p
             INNER JOIN users u ON u.id = p.user_id
             WHERE p.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();

        return $post ?: null;
    }

    public function updateByOwner(
        int $postId,
        int $ownerId,
        string $postType,
        string $title,
        string $content,
        ?string $mediaPath,
        string $mediaType,
        string $mediaSize,
        ?string $location,
        string $visibility,
        ?string $scheduledAt
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE posts
             SET post_type = :post_type,
                 title = :title,
                 content = :content,
                 image_path = :image_path,
                 media_type = :media_type,
                 media_size = :media_size,
                 location = :location,
                 visibility = :visibility,
                 scheduled_at = :scheduled_at,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :post_id AND user_id = :owner_id'
        );

        return $stmt->execute([
            'post_type' => $postType,
            'title' => $title !== '' ? $title : null,
            'content' => $content,
            'image_path' => $mediaPath,
            'media_type' => $mediaType,
            'media_size' => $mediaSize,
            'location' => $location !== '' ? $location : null,
            'visibility' => $visibility,
            'scheduled_at' => $scheduledAt,
            'post_id' => $postId,
            'owner_id' => $ownerId,
        ]);
    }

//like  etc

public function toggleLike(int $postId, int $userId): bool
{
    $check = $this->pdo->prepare(
        'SELECT 1 FROM post_likes WHERE post_id = :post_id AND user_id = :user_id LIMIT 1'
    );
    $check->execute([
        'post_id' => $postId,
        'user_id' => $userId,
    ]);

    $alreadyLiked = (bool) $check->fetchColumn();

    if ($alreadyLiked) {
        $del = $this->pdo->prepare(
            'DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id'
        );
        return $del->execute([
            'post_id' => $postId,
            'user_id' => $userId,
        ]);
    }

    $ins = $this->pdo->prepare(
        'INSERT INTO post_likes (post_id, user_id) VALUES (:post_id, :user_id)'
    );
    return $ins->execute([
        'post_id' => $postId,
        'user_id' => $userId,
    ]);
}

    public function likesCountByPostId(int $postId): int
{
    $stmt = $this->pdo->prepare(
        'SELECT COUNT(*) FROM post_likes WHERE post_id = :post_id'
    );
    $stmt->execute(['post_id' => $postId]);
    return (int) $stmt->fetchColumn();
}

public function isLikedByUser(int $postId, int $userId): bool
{
    $stmt = $this->pdo->prepare(
        'SELECT 1 FROM post_likes WHERE post_id = :post_id AND user_id = :user_id LIMIT 1'
    );
    $stmt->execute([
        'post_id' => $postId,
        'user_id' => $userId,
    ]);
    return (bool) $stmt->fetchColumn();
}

public function hasInterestByUser(int $postId, int $userId): bool
{
    $stmt = $this->pdo->prepare(
        'SELECT 1
         FROM post_interests
         WHERE post_id = :post_id AND user_id = :user_id
         LIMIT 1'
    );
    $stmt->execute([
        'post_id' => $postId,
        'user_id' => $userId,
    ]);

    return (bool) $stmt->fetchColumn();
}

public function interestCountByPostId(int $postId): int
{
    $stmt = $this->pdo->prepare(
        'SELECT COUNT(*)
         FROM post_interests
         WHERE post_id = :post_id'
    );
    $stmt->execute(['post_id' => $postId]);

    return (int) $stmt->fetchColumn();
}

public function addInterest(int $postId, int $userId): bool
{
    // INSERT IGNORE + contrainte unique = protection simple contre les doublons.
    $stmt = $this->pdo->prepare(
        'INSERT IGNORE INTO post_interests (post_id, user_id)
         VALUES (:post_id, :user_id)'
    );

    $stmt->execute([
        'post_id' => $postId,
        'user_id' => $userId,
    ]);

    return $stmt->rowCount() > 0;
}

public function deleteByOwner(int $postId, int $ownerId): bool
{
    $stmt = $this->pdo->prepare(
        'DELETE FROM posts WHERE id = :post_id AND user_id = :owner_id'
    );

    return $stmt->execute([
        'post_id' => $postId,
        'owner_id' => $ownerId,
    ]);
}

public function latestForAdmin(int $limit = 100): array
{
    $stmt = $this->pdo->prepare(
        'SELECT p.id, p.user_id, p.title, p.content, p.visibility, p.created_at, u.username
         , p.post_type, p.scheduled_at
         FROM posts p
         INNER JOIN users u ON u.id = p.user_id
         ORDER BY p.created_at DESC
         LIMIT :lim'
    );
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

public function searchForAdmin(array $filters = [], int $limit = 25, int $offset = 0): array
{
    $params = [];
    $where = $this->adminPostsWhere($filters, $params);

    $stmt = $this->pdo->prepare(
        'SELECT p.id, p.user_id, p.title, p.content, p.visibility, p.created_at, u.username,
                p.post_type, p.scheduled_at, p.location, p.media_type, p.image_path
         FROM posts p
         INNER JOIN users u ON u.id = p.user_id
         ' . $where . '
         ORDER BY p.created_at DESC, p.id DESC
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
    $where = $this->adminPostsWhere($filters, $params);

    $stmt = $this->pdo->prepare(
        'SELECT COUNT(*)
         FROM posts p
         INNER JOIN users u ON u.id = p.user_id
         ' . $where
    );
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

public function deleteByAdmin(int $postId): bool
{
    $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');

    return $stmt->execute(['id' => $postId]);
}

private function adminPostsWhere(array $filters, array &$params): string
{
    $clauses = ['WHERE 1=1'];
    $query = trim((string) ($filters['query'] ?? ''));
    $visibility = (string) ($filters['visibility'] ?? '');
    $postType = (string) ($filters['post_type'] ?? '');

    if ($query !== '') {
        $params['query_title_like'] = '%' . $query . '%';
        $params['query_content_like'] = '%' . $query . '%';
        $params['query_username_like'] = '%' . $query . '%';
        $params['query_location_like'] = '%' . $query . '%';
        $params['query_id_like'] = '%' . $query . '%';
        $clauses[] = 'AND (
            p.title LIKE :query_title_like
            OR p.content LIKE :query_content_like
            OR u.username LIKE :query_username_like
            OR COALESCE(p.location, \'\') LIKE :query_location_like
            OR CAST(p.id AS CHAR) LIKE :query_id_like
        )';
    }

    if (in_array($visibility, ['public', 'friends', 'private'], true)) {
        $params['visibility'] = $visibility;
        $clauses[] = 'AND p.visibility = :visibility';
    }

    if (in_array($postType, ['publication', 'entrainement'], true)) {
        $params['post_type'] = $postType;
        $clauses[] = 'AND p.post_type = :post_type';
    }

    return implode("\n", $clauses);
}

public function latestPublicByUserId(int $userId, int $limit = 12, int $offset = 0): array
{
    $stmt = $this->pdo->prepare(
        'SELECT id, user_id, post_type, title, content, image_path, media_type, media_size, location, scheduled_at, visibility, created_at
         FROM posts
         WHERE user_id = :user_id AND visibility = :visibility
         ORDER BY created_at DESC
         LIMIT :lim OFFSET :off'
    );
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':visibility', 'public', PDO::PARAM_STR);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

public function publicCountByUserId(int $userId): int
{
    $stmt = $this->pdo->prepare(
        'SELECT COUNT(*)
         FROM posts
         WHERE user_id = :user_id AND visibility = :visibility'
    );
    $stmt->execute([
        'user_id' => $userId,
        'visibility' => 'public',
    ]);

    return (int) $stmt->fetchColumn();
}
}
