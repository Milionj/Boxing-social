<?php
declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use PDO;
use PDOException;

/**
 * Modèle Friendship
 * -----------------
 * Cette classe gère les relations "amis" entre utilisateurs.
 *
 * Table utilisée : friendships
 * - requester_id = celui qui envoie la demande
 * - addressee_id = celui qui reçoit la demande
 * - status = pending / accepted / declined / blocked
 *
 * Le modèle s'occupe UNIQUEMENT des requêtes SQL.
 * La validation "métier" plus complète (messages, redirections, auth, etc.)
 * se fait dans le Controller.
 */
final class Friendship
{
    /**
     * Connexion PDO utilisée par toutes les méthodes de ce modèle.
     */
    private PDO $pdo;

    public function __construct()
    {
        // On récupère la connexion DB (singleton défini dans Database::getConnection()).
        // Cela évite de recréer une connexion dans chaque méthode.
        $this->pdo = Database::getConnection();
    }

    /**
     * Envoie une demande d'ami.
     *
     * @param int $requesterId  ID de l'utilisateur qui envoie la demande
     * @param int $addresseeId  ID de l'utilisateur qui reçoit la demande
     *
     * @return bool true si insertion OK, false sinon
     */
    public function sendRequest(int $requesterId, int $addresseeId): bool
    {
        // Sécurité / logique métier minimale :
        // un utilisateur ne peut pas s'ajouter lui-même.
        if ($requesterId === $addresseeId) {
            return false;
        }

        // Evite les exceptions SQL sur doublon de relation.
        if ($this->requestExistsBetween($requesterId, $addresseeId)) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO friendships (requester_id, addressee_id, status)
                 VALUES (:requester_id, :addressee_id, :status)'
            );

            return $stmt->execute([
                'requester_id' => $requesterId,
                'addressee_id' => $addresseeId,
                'status' => 'pending',
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * Liste des demandes reçues (entrantes) pour un utilisateur.
     *
     * Exemple :
     * - Si user #5 reçoit une demande de user #2,
     *   cette méthode retournera cette ligne pour userId=5.
     *
     * @param int $userId
     * @return array<int, array<string, mixed>>
     */
    public function incomingRequests(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.id, f.requester_id, f.addressee_id, f.status, f.created_at, u.username AS requester_username
             FROM friendships f
             INNER JOIN users u ON u.id = f.requester_id
             WHERE f.addressee_id = :user_id AND f.status = :status
             ORDER BY f.created_at DESC'
        );

        // On ne récupère ici que les demandes "pending" (en attente)
        // dont l'utilisateur est destinataire.
        $stmt->execute([
            'user_id' => $userId,
            'status' => 'pending',
        ]);

        // fetchAll() retourne un tableau de lignes.
        // Si aucune demande => on renvoie [] (tableau vide) pour simplifier les templates.
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Liste des demandes envoyées (sortantes) par un utilisateur.
     *
     * Exemple :
     * - Si user #5 a envoyé une demande à user #8,
     *   cette méthode retournera cette ligne pour userId=5.
     *
     * @param int $userId
     * @return array<int, array<string, mixed>>
     */
    public function outgoingRequests(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.id, f.requester_id, f.addressee_id, f.status, f.created_at, u.username AS addressee_username
             FROM friendships f
             INNER JOIN users u ON u.id = f.addressee_id
             WHERE f.requester_id = :user_id AND f.status = :status
             ORDER BY f.created_at DESC'
        );

        // On ne récupère ici que les demandes "pending"
        // envoyées par l'utilisateur courant.
        $stmt->execute([
            'user_id' => $userId,
            'status' => 'pending',
        ]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Permet au destinataire de répondre à une demande (accepter / refuser).
     *
     * Règles appliquées ici :
     * - Seuls les statuts "accepted" et "declined" sont autorisés
     * - Seul le destinataire (addressee) peut modifier la demande
     * - La demande doit être encore en "pending"
     *
     * @param int $friendshipId
     * @param int $addresseeId
     * @param string $status    accepted | declined
     *
     * @return bool true si la requête SQL s'exécute, false sinon
     */
    public function updateStatusByAddressee(int $friendshipId, int $addresseeId, string $status): bool
    {
        // Liste blanche des statuts autorisés pour cette action.
        // On interdit ici "blocked" (à gérer par une autre méthode plus tard si besoin).
        $allowed = ['accepted', 'declined'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE friendships
             SET status = :status, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND addressee_id = :addressee_id AND status = :pending'
        );

        // Condition importante :
        // - id = la bonne demande
        // - addressee_id = le user connecté est bien le destinataire
        // - status = pending (on ne re-traite pas une demande déjà acceptée/refusée)
        return $stmt->execute([
            'status' => $status,
            'id' => $friendshipId,
            'addressee_id' => $addresseeId,
            'pending' => 'pending',
        ]);
    }

    /**
     * Retourne la liste des amis d'un utilisateur.
     *
     * Ici, une "amitié" = une ligne friendships avec status = accepted.
     * Comme la relation peut être dans les deux sens (requester -> addressee),
     * on récupère l'autre utilisateur via une jointure avec OR.
     *
     * @param int $userId
     * @return array<int, array<string, mixed>>
     */
    public function friendsOf(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.username
             FROM friendships f
             INNER JOIN users u
                ON (u.id = f.requester_id AND f.addressee_id = :uid_a)
                OR (u.id = f.addressee_id AND f.requester_id = :uid_b)
             WHERE f.status = :accepted AND u.id <> :uid_c
             ORDER BY u.username ASC'
        );

        // On filtre seulement les relations acceptées.
        $stmt->execute([
            'uid_a' => $userId,
            'uid_b' => $userId,
            'uid_c' => $userId,
            'accepted' => 'accepted',
        ]);

        return $stmt->fetchAll() ?: [];
    }

    private function requestExistsBetween(int $userA, int $userB): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM friendships
             WHERE (requester_id = :a1 AND addressee_id = :b1)
                OR (requester_id = :b2 AND addressee_id = :a2)
             LIMIT 1'
        );

        $stmt->execute([
            'a1' => $userA,
            'b1' => $userB,
            'b2' => $userB,
            'a2' => $userA,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
