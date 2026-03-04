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
}
