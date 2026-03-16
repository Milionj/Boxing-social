<?php
declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use PDO;

/**
 * Modèle Message
 * --------------
 * Gère la messagerie privée :
 * - vérifier qu'un utilisateur existe
 * - lister les conversations d'un utilisateur
 * - récupérer les messages d'une conversation (2 personnes)
 * - envoyer un message
 * - marquer une conversation comme lue
 */
final class Message
{
    /**
     * Connexion PDO utilisée pour toutes les requêtes de messagerie
     */
    private PDO $pdo;

    public function __construct()
    {
        // Récupère la connexion DB (singleton)
        $this->pdo = Database::getConnection();
    }

    /**
     * Vérifie qu'un utilisateur existe.
     * Utile avant d'envoyer un message vers un destinataire.
     *
     * @param int $userId
     * @return bool true si l'utilisateur existe
     */
    public function userExists(int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);

        // fetchColumn() => "1" si trouvé, false sinon
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Retourne la liste des conversations d'un utilisateur.
     *
     * Idée :
     * - On cherche tous les messages où l'utilisateur est soit sender, soit receiver
     * - On identifie "l'autre utilisateur" avec un CASE
     * - On groupe par interlocuteur
     * - On trie par date du dernier message
     *
     * Résultat :
     * - other_user_id
     * - username
     * - last_message_at
     */
    public function getConversations(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id AS other_user_id, u.username, MAX(m.created_at) AS last_message_at
             FROM messages m
             INNER JOIN users u
               ON u.id = CASE
                    WHEN m.sender_id = :me1 THEN m.receiver_id
                    ELSE m.sender_id
               END
             WHERE m.sender_id = :me2 OR m.receiver_id = :me3
             GROUP BY u.id, u.username
             ORDER BY last_message_at DESC'
        );

        // On passe plusieurs placeholders "me" (même valeur, noms différents)
        $stmt->execute([
            'me1' => $userId,
            'me2' => $userId,
            'me3' => $userId,
        ]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Retourne tous les messages entre 2 utilisateurs (conversation privée).
     *
     * On prend les messages dans les 2 sens :
     * - moi -> autre
     * - autre -> moi
     *
     * Tri ASC : plus ancien -> plus récent (lecture naturelle d'une conversation)
     */
    public function getConversationMessages(int $userId, int $otherUserId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, sender_id, receiver_id, content, is_read, created_at
             FROM messages
             WHERE (sender_id = :me1 AND receiver_id = :other1)
                OR (sender_id = :other2 AND receiver_id = :me2)
             ORDER BY created_at ASC'
        );

        $stmt->execute([
            'me1' => $userId,
            'other1' => $otherUserId,
            'other2' => $otherUserId,
            'me2' => $userId,
        ]);

        return $stmt->fetchAll() ?: [];
    }

    public function findById(int $messageId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, sender_id, receiver_id, content, is_read, created_at
             FROM messages
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $messageId]);

        $message = $stmt->fetch();

        return $message ?: null;
    }

    public function create(int $senderId, int $receiverId, string $content): ?array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO messages (sender_id, receiver_id, content, is_read)
             VALUES (:sender_id, :receiver_id, :content, 0)'
        );

        $ok = $stmt->execute([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'content' => $content,
        ]);

        if (!$ok) {
            return null;
        }

        $messageId = (int) $this->pdo->lastInsertId();

        return $messageId > 0 ? $this->findById($messageId) : null;
    }

    /**
     * Envoie un message (création en base).
     *
     * is_read = 0 à la création (non lu par défaut)
     */
    public function send(int $senderId, int $receiverId, string $content): bool
    {
        return $this->create($senderId, $receiverId, $content) !== null;
    }

    /**
     * Marque comme lus tous les messages reçus d'un interlocuteur donné.
     *
     * Cas d'usage :
     * - Quand j'ouvre la conversation avec X,
     *   les messages envoyés par X vers moi passent en is_read = 1.
     */
    public function markConversationRead(int $userId, int $otherUserId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE messages
             SET is_read = 1
             WHERE sender_id = :other_user
               AND receiver_id = :me
               AND is_read = 0'
        );

        return $stmt->execute([
            'other_user' => $otherUserId,
            'me' => $userId,
        ]);
    }
}
