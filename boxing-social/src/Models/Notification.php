<?php
declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use PDO;
use PDOException;

/**
 * Modèle Notification
 * -------------------
 * Gère les notifications "in-app" :
 * - création
 * - listing des plus récentes
 * - compteur non lues
 * - marquage lu / tout lu
 */
final class Notification
{
    /**
     * Connexion PDO réutilisée par toutes les méthodes
     */
    private PDO $pdo;

    public function __construct()
    {
        // Récupère la connexion DB (singleton)
        $this->pdo = Database::getConnection();
    }

    /**
     * Crée une notification.
     *
     * @param int      $userId    Destinataire de la notification
     * @param ?int     $actorId   Auteur de l'action (NULL possible pour notif système)
     * @param string   $type      Type de notif (like/comment/friend_request/etc.)
     * @param ?int     $entityId  ID lié (post/comment/message...), optionnel
     * @param ?string  $content   Texte court prêt à afficher (optionnel)
     *
     * @return bool true si insert OK, false sinon
     */
    public function create(
        int $userId,
        ?int $actorId,
        string $type,
        ?int $entityId,
        ?string $content
    ): bool {
        // Si la table de preferences existe et que l'utilisateur a coupe les notifications,
        // on considère l'action comme "OK" mais on n'insere rien.
        if (!$this->notificationsEnabledForUser($userId)) {
            return true;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO notifications (user_id, actor_id, type, entity_id, content, is_read)
             VALUES (:user_id, :actor_id, :type, :entity_id, :content, 0)'
        );

        // is_read = 0 à la création => notification non lue
        return $stmt->execute([
            'user_id' => $userId,
            'actor_id' => $actorId,
            'type' => $type,
            'entity_id' => $entityId,
            'content' => $content,
        ]);
    }

    /**
     * Retourne les notifications les plus récentes d'un utilisateur.
     *
     * LEFT JOIN users :
     * - permet de récupérer le username de l'acteur
     * - actor_id peut être NULL (notification système), donc LEFT JOIN est adapté
     *
     * @param int $userId
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function latestForUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT n.id, n.user_id, n.actor_id, n.type, n.entity_id, n.content, n.is_read, n.created_at,
                    u.username AS actor_username
             FROM notifications n
             LEFT JOIN users u ON u.id = n.actor_id
             WHERE n.user_id = :user_id
             ORDER BY n.created_at DESC
             LIMIT :lim'
        );

        // bindValue + PARAM_INT => important pour les champs numériques (surtout LIMIT)
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Retourne une notification précise si elle appartient bien à l'utilisateur.
     *
     * @return array<string, mixed>|null
     */
    public function findByIdForOwner(int $notificationId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT n.id, n.user_id, n.actor_id, n.type, n.entity_id, n.content, n.is_read, n.created_at,
                    u.username AS actor_username
             FROM notifications n
             LEFT JOIN users u ON u.id = n.actor_id
             WHERE n.id = :id AND n.user_id = :user_id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId,
        ]);

        $notification = $stmt->fetch();

        return $notification ?: null;
    }

    /**
     * Compte le nombre de notifications non lues pour un utilisateur.
     * Utile pour le badge dans la navbar.
     */
    public function unreadCount(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0'
        );

        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Marque une notification comme lue si elle appartient à l'utilisateur.
     * (Sécurité : on empêche de modifier les notifs d'un autre user)
     */
    public function markReadByOwner(int $notificationId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE id = :id AND user_id = :user_id'
        );

        return $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues.
     * Utile pour un bouton "Tout marquer comme lu".
     */
    public function markAllRead(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notifications SET is_read = 1 WHERE user_id = :user_id'
        );

        return $stmt->execute(['user_id' => $userId]);
    }

    private function notificationsEnabledForUser(int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT notifications_enabled
                 FROM user_settings
                 WHERE user_id = :user_id
                 LIMIT 1'
            );
            $stmt->execute(['user_id' => $userId]);
            $value = $stmt->fetchColumn();

            if ($value === false) {
                return true;
            }

            return (int) $value === 1;
        } catch (PDOException) {
            // Si la table user_settings n'existe pas encore, on conserve le comportement historique.
            return true;
        }
    }
}
