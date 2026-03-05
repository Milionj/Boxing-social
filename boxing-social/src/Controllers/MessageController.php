<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Message;
use App\Models\Notification;

/**
 * MessageController
 * -----------------
 * Gère la messagerie privée :
 * - afficher les conversations et un fil sélectionné
 * - envoyer un message
 *
 * Le controller :
 * - vérifie l'authentification
 * - valide les inputs (user_id, content)
 * - délègue la logique SQL aux modèles Message / Notification
 * - gère les messages flash + redirections (pattern PRG)
 */
final class MessageController
{
    /**
     * Modèles utilisés :
     * - Message : conversations + envoi + lecture
     * - Notification : notifier le destinataire d'un nouveau message
     */
    private Message $messages;
    private Notification $notifications;

    public function __construct()
    {
        // Instanciation des modèles (accès DB)
        $this->messages = new Message();
        $this->notifications = new Notification();
    }

    /**
     * Guard d'authentification :
     * - vérifie qu'un utilisateur est connecté
     * - sinon redirection vers /login
     *
     * @return int|null ID user si connecté, sinon null
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
     * GET /messages
     * GET /messages?user_id=X
     *
     * Affiche :
     * - la liste des conversations de l'utilisateur connecté
     * - éventuellement le fil de discussion avec l'utilisateur X
     *
     * Si un fil est ouvert :
     * - charge les messages
     * - marque les messages reçus comme lus
     */
    public function index(Request $request, Response $response): void
    {
        // 1) Auth obligatoire
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        // 2) Liste des conversations (interlocuteurs + date dernier message)
        $conversations = $this->messages->getConversations($userId);

        // 3) Paramètre optionnel : user_id de l'interlocuteur à afficher
        $selectedUserId = (int) $request->input('user_id', 0);

        // Fil de discussion (vide par défaut)
        $thread = [];

        // 4) Validation de l'interlocuteur sélectionné :
        // - id > 0
        // - pas soi-même
        // - utilisateur existant
        if (
            $selectedUserId > 0 &&
            $selectedUserId !== $userId &&
            $this->messages->userExists($selectedUserId)
        ) {
            // Charge tous les messages de la conversation (dans les 2 sens)
            $thread = $this->messages->getConversationMessages($userId, $selectedUserId);

            // Marque comme lus les messages reçus de cet interlocuteur
            $this->messages->markConversationRead($userId, $selectedUserId);
        } else {
            // Si user_id invalide, on "désélectionne" la conversation
            $selectedUserId = 0;
        }

        // 5) Messages flash (succès / erreurs), affichés une seule fois
        $errors = $_SESSION['errors_messages'] ?? [];
        $success = $_SESSION['success_messages'] ?? '';

        unset($_SESSION['errors_messages'], $_SESSION['success_messages']);

        // 6) Affichage du template de messagerie
        require dirname(__DIR__, 2) . '/templates/messages/index.php';
    }

    /**
     * POST /messages/send
     *
     * Envoie un message à un destinataire :
     * - vérifie auth
     * - valide receiver_id + contenu
     * - insère le message
     * - crée une notification pour le destinataire
     * - redirige vers la conversation
     */
    public function send(Request $request, Response $response): void
    {
        // 1) Auth obligatoire
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        // 2) Récupération des inputs
        $receiverId = (int) $request->input('receiver_id', 0);
        $content = trim((string) $request->input('content', ''));

        // 3) Validation destinataire
        // - id valide
        // - on ne s'envoie pas de message à soi-même
        if ($receiverId <= 0 || $receiverId === $userId) {
            $_SESSION['errors_messages'] = ['Destinataire invalide.'];
            $response->redirect('/messages');
            return;
        }

        // Vérifie que le destinataire existe
        if (!$this->messages->userExists($receiverId)) {
            $_SESSION['errors_messages'] = ['Utilisateur introuvable.'];
            $response->redirect('/messages');
            return;
        }

        // 4) Validation contenu message
        if ($content === '' || strlen($content) < 1) {
            $_SESSION['errors_messages'] = ['Le message est vide.'];
            $response->redirect('/messages?user_id=' . $receiverId);
            return;
        }

        // 5) Envoi en DB
        $ok = $this->messages->send($userId, $receiverId, $content);

        if (!$ok) {
            $_SESSION['errors_messages'] = ['Envoi impossible.'];
            $response->redirect('/messages?user_id=' . $receiverId);
            return;
        }

        // 6) Notification au destinataire (message reçu)
        $this->notifications->create(
            $receiverId, // destinataire de la notification
            $userId,     // acteur (celui qui a envoyé le message)
            'message',
            null,        // pas d'entity_id utilisé ici (possible plus tard)
            'Vous avez recu un nouveau message.'
        );

        // 7) Message flash succès + retour sur la conversation
        $_SESSION['success_messages'] = 'Message envoye.';
        $response->redirect('/messages?user_id=' . $receiverId);
    }
}