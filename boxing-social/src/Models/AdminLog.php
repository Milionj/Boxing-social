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

    public function search(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        $params = [];
        $where = $this->adminLogsWhere($filters, $params);

        $stmt = $this->pdo->prepare(
            'SELECT l.id, l.admin_id, l.action, l.target_type, l.target_id, l.details, l.created_at, u.username AS admin_username
             FROM admin_logs l
             INNER JOIN users u ON u.id = l.admin_id
             ' . $where . '
             ORDER BY l.created_at DESC, l.id DESC
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

    public function count(array $filters = []): int
    {
        $params = [];
        $where = $this->adminLogsWhere($filters, $params);

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM admin_logs l
             INNER JOIN users u ON u.id = l.admin_id
             ' . $where
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    private function adminLogsWhere(array $filters, array &$params): string
    {
        $clauses = ['WHERE 1=1'];
        $query = trim((string) ($filters['query'] ?? ''));
        $action = trim((string) ($filters['action'] ?? ''));
        $targetType = trim((string) ($filters['target_type'] ?? ''));

        if ($query !== '') {
            $params['query_admin_like'] = '%' . $query . '%';
            $params['query_action_like'] = '%' . $query . '%';
            $params['query_target_type_like'] = '%' . $query . '%';
            $params['query_target_id_like'] = '%' . $query . '%';
            $params['query_details_like'] = '%' . $query . '%';
            $clauses[] = 'AND (
                u.username LIKE :query_admin_like
                OR l.action LIKE :query_action_like
                OR l.target_type LIKE :query_target_type_like
                OR CAST(l.target_id AS CHAR) LIKE :query_target_id_like
                OR COALESCE(l.details, \'\') LIKE :query_details_like
            )';
        }

        if ($action !== '') {
            $params['action'] = $action;
            $clauses[] = 'AND l.action = :action';
        }

        if ($targetType !== '') {
            $params['target_type'] = $targetType;
            $clauses[] = 'AND l.target_type = :target_type';
        }

        return implode("\n", $clauses);
    }
}
