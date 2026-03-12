<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Notification;

/**
 * NotificationController
 * ----------------------
 * Gère l'affichage et les actions sur les notifications :
 * - afficher la liste des notifications
 * - marquer une notification comme lue
 * - marquer toutes les notifications comme lues
 *
 * Le controller :
 * - vérifie l'authentification
 * - récupère les données via le modèle Notification
 * - redirige après les actions POST (pattern PRG)
 */
final class NotificationController
{
    /**
     * Modèle Notification (accès DB pour la table notifications)
     */
    private Notification $notifications;

    public function __construct()
    {
        // Instancie le modèle (qui récupère la connexion PDO)
        $this->notifications = new Notification();
    }

    /**
     * Guard d'authentification :
     * - vérifie qu'un utilisateur est connecté
     * - sinon redirection vers /login
     *
     * @return int|null ID utilisateur si connecté, sinon null
     */
    private function requireAuth(Response $response): ?int
    {
        $id = $_SESSION['user']['id'] ?? null;

        if (!is_int($id)) {
            $response->redirect('/login');
            return null;
        }

        return $id;
    }

    /**
     * GET /notifications
     * - affiche les notifications récentes
     * - calcule le nombre de notifications non lues (badge / compteur)
     */
    public function index(Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        // Liste des notifications (80 max ici, choix MVP)
        $items = $this->notifications->latestForUser($userId, 80);
        foreach ($items as &$item) {
            $item['target_url'] = $this->resolveTargetUrl($item);
        }
        unset($item);

        // Compteur des notifications non lues (utile pour badge/navbar)
        $unreadCount = $this->notifications->unreadCount($userId);

        // Affiche le template
        require dirname(__DIR__, 2) . '/templates/notifications/index.php';
    }

    private function resolveTargetUrl(array $notification): string
    {
        $type = (string) ($notification['type'] ?? '');
        $entityId = (int) ($notification['entity_id'] ?? 0);
        $actorUsername = (string) ($notification['actor_username'] ?? '');

        if (($type === 'like' || $type === 'comment') && $entityId > 0) {
            return '/post?id=' . $entityId;
        }

        if ($type === 'message') {
            return $actorUsername !== '' ? '/messages?username=' . rawurlencode($actorUsername) : '/messages';
        }

        if ($type === 'friend_request' || $type === 'friend_accept') {
            return $actorUsername !== '' ? '/user?username=' . rawurlencode($actorUsername) : '/friends';
        }

        return '/notifications';
    }

    /**
     * POST /notifications/read
     * Marque UNE notification comme lue (si elle appartient à l'utilisateur connecté)
     */
    public function markRead(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        // ID de la notification à marquer comme lue
        $id = (int) $request->input('notification_id', 0);

        // Validation minimale : ID > 0
        if ($id > 0) {
            // Sécurité gérée aussi dans le modèle (WHERE id = :id AND user_id = :user_id)
            $this->notifications->markReadByOwner($id, $userId);
        }

        // Redirection (pattern PRG : POST -> Redirect -> GET)
        $response->redirect('/notifications');
    }

    /**
     * POST /notifications/read-all
     * Marque toutes les notifications de l'utilisateur comme lues
     */
    public function markAllRead(Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        $this->notifications->markAllRead($userId);

        // Retour à la page notifications
        $response->redirect('/notifications');
    }
}
