<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Notification;
use App\Services\NotificationService;

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
    private NotificationService $notificationService;

    public function __construct()
    {
        // Instancie le modèle (qui récupère la connexion PDO)
        $this->notifications = new Notification();
        $this->notificationService = new NotificationService();
    }

    /**
     * Guard d'authentification :
     * - vérifie qu'un utilisateur est connecté
     * - sinon redirection vers /login
     *
     * @return int|null ID utilisateur si connecté, sinon null
     */
    private function requireAuth(Response $response, ?Request $request = null): ?int
    {
        $id = $_SESSION['user']['id'] ?? null;

        if (!is_int($id)) {
            if ($request?->expectsJson()) {
                $response->json([
                    'ok' => false,
                    'message' => 'Connexion requise.',
                ], 401);
                return null;
            }

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

    /**
     * GET /notifications/open
     * Marque la notification comme lue puis redirige vers la cible associée.
     */
    public function open(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response, $request);
        if ($userId === null) {
            return;
        }

        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            $response->redirect('/notifications');
            return;
        }

        $notification = $this->notifications->findByIdForOwner($id, $userId);
        if ($notification === null) {
            $response->redirect('/notifications');
            return;
        }

        if ((int) ($notification['is_read'] ?? 0) === 0) {
            $this->notifications->markReadByOwner($id, $userId);
        }

        $response->redirect($this->notificationService->resolveTargetUrl($notification));
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
        $userId = $this->requireAuth($response, $request);
        if ($userId === null) {
            return;
        }

        // ID de la notification à marquer comme lue
        $id = (int) $request->input('notification_id', 0);

        // Validation minimale : ID > 0
        if ($id <= 0) {
            if ($request->expectsJson()) {
                $response->json([
                    'ok' => false,
                    'message' => 'Notification introuvable.',
                ], 422);
                return;
            }

            $this->redirectBack($request, $response);
            return;
        }

        // Sécurité gérée aussi dans le modèle (WHERE id = :id AND user_id = :user_id)
        $this->notifications->markReadByOwner($id, $userId);

        if ($request->expectsJson()) {
            $response->json([
                'ok' => true,
                'notificationId' => $id,
                'unreadCount' => $this->notifications->unreadCount($userId),
            ]);
            return;
        }

        // Redirection (pattern PRG : POST -> Redirect -> GET)
        $this->redirectBack($request, $response);
    }

    /**
     * POST /notifications/read-all
     * Marque toutes les notifications de l'utilisateur comme lues
     */
    public function markAllRead(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response, $request);
        if ($userId === null) {
            return;
        }

        $this->notifications->markAllRead($userId);

        if ($request->expectsJson()) {
            $response->json([
                'ok' => true,
                'unreadCount' => 0,
            ]);
            return;
        }

        // Retour à la page courante ou au centre de notifications.
        $this->redirectBack($request, $response);
    }

    private function redirectBack(Request $request, Response $response): void
    {
        $redirectTo = (string) $request->input('redirect_to', '/notifications');

        if ($redirectTo === '' || !str_starts_with($redirectTo, '/')) {
            $redirectTo = '/notifications';
        }

        $response->redirect($redirectTo);
    }
}
