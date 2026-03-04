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
        string $title,
        string $content,
        ?string $imagePath,
        ?string $location,
        string $visibility = 'public'
    ): bool {
        $stmt = $this->pdo->prepare(
            'INSERT INTO posts (user_id, title, content, image_path, location, visibility)
             VALUES (:user_id, :title, :content, :image_path, :location, :visibility)'
        );

        return $stmt->execute([
            'user_id' => $userId,
            'title' => $title !== '' ? $title : null,
            'content' => $content,
            'image_path' => $imagePath,
            'location' => $location !== '' ? $location : null,
            'visibility' => $visibility,
        ]);
    }

    public function latestFeed(int $limit = 20): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                p.id,
                p.user_id,
                p.title,
                p.content,
                p.image_path,
                p.location,
                p.visibility,
                p.created_at,
                u.username
             FROM posts p
             INNER JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }
    public function findById(int $id): ?array
{
    $stmt = $this->pdo->prepare(
        'SELECT id, user_id, title, content, image_path, location, visibility, created_at
         FROM posts
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch();

    return $post ?: null;
}

// mise a jour d'un post par son proprietaire

public function updateByOwner(
    int $postId,
    int $ownerId,
    string $title,
    string $content,
    ?string $location,
    string $visibility
): bool {
    $stmt = $this->pdo->prepare(
        'UPDATE posts
         SET title = :title, content = :content, location = :location, visibility = :visibility, updated_at = CURRENT_TIMESTAMP
         WHERE id = :post_id AND user_id = :owner_id'
    );

    return $stmt->execute([
        'title' => $title !== '' ? $title : null,
        'content' => $content,
        'location' => $location !== '' ? $location : null,
        'visibility' => $visibility,
        'post_id' => $postId,
        'owner_id' => $ownerId,
    ]);
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
}
