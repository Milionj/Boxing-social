<?php
declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use PDO;

final class AdminLog
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function create(
        int $adminId,
        string $action,
        string $targetType,
        ?int $targetId = null,
        ?string $detailsJson = null
    ): bool {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admin_logs (admin_id, action, target_type, target_id, details)
             VALUES (:admin_id, :action, :target_type, :target_id, :details)'
        );

        return $stmt->execute([
            'admin_id' => $adminId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $detailsJson,
        ]);
    }

    public function latest(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT l.id, l.admin_id, l.action, l.target_type, l.target_id, l.details, l.created_at, u.username AS admin_username
             FROM admin_logs l
             INNER JOIN users u ON u.id = l.admin_id
             ORDER BY l.created_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }
}
