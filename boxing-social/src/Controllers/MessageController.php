<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;

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
    private User $users;

    public function __construct()
    {
        // Instanciation des modèles (accès DB)
        $this->messages = new Message();
        $this->notifications = new Notification();
        $this->users = new User();
    }

    /**
     * Guard d'authentification :
     * - vérifie qu'un utilisateur est connecté
     * - sinon redirection vers /login
     *
     * @return int|null ID user si connecté, sinon null
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

    private function contentLength(string $content): int
    {
        return function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
    }

    private function redirectToConversation(Response $response, int $receiverId = 0): void
    {
        $path = '/messages';
        if ($receiverId > 0) {
            $path .= '?user_id=' . $receiverId;
        }

        $response->redirect($path);
    }

    private function respondSendError(
        Request $request,
        Response $response,
        string $message,
        int $receiverId = 0,
        int $status = 422
    ): void {
        if ($request->expectsJson()) {
            $response->json([
                'ok' => false,
                'message' => $message,
            ], $status);
            return;
        }

        $_SESSION['errors_messages'] = [$message];
        $this->redirectToConversation($response, $receiverId);
    }

    private function messagePayload(array $message, int $currentUserId): array
    {
        return [
            'id' => (int) $message['id'],
            'senderId' => (int) $message['sender_id'],
            'receiverId' => (int) $message['receiver_id'],
            'content' => (string) $message['content'],
            'createdAt' => (string) $message['created_at'],
            'isMine' => (int) $message['sender_id'] === $currentUserId,
            'isRead' => (int) $message['is_read'] === 1,
        ];
    }

    private function conversationPayload(array $receiver, string $lastMessageAt = '', int $unreadCount = 0): array
    {
        $receiverId = (int) $receiver['id'];
        $username = (string) $receiver['username'];

        return [
            'userId' => $receiverId,
            'username' => $username,
            'threadUrl' => '/messages?user_id=' . $receiverId,
            'profileUrl' => '/user?username=' . rawurlencode($username),
            'lastMessageAt' => $lastMessageAt,
            'unreadCount' => $unreadCount,
        ];
    }

    private function conversationListPayload(array $conversation): array
    {
        return [
            'userId' => (int) $conversation['other_user_id'],
            'username' => (string) $conversation['username'],
            'threadUrl' => '/messages?user_id=' . (int) $conversation['other_user_id'],
            'profileUrl' => '/user?username=' . rawurlencode((string) $conversation['username']),
            'lastMessageAt' => (string) ($conversation['last_message_at'] ?? ''),
            'unreadCount' => max(0, (int) ($conversation['unread_count'] ?? 0)),
        ];
    }

    private function serializeConversations(array $conversations): array
    {
        return array_map(
            fn(array $conversation): array => $this->conversationListPayload($conversation),
            $conversations
        );
    }

    private function hasConversationSelection(Request $request): bool
    {
        return (int) $request->input('user_id', 0) > 0
            || trim((string) $request->input('username', '')) !== '';
    }

    private function resolveSelectedConversation(Request $request, int $currentUserId): array
    {
        $selectedUsername = trim((string) $request->input('username', ''));
        $selectedUserId = (int) $request->input('user_id', 0);

        if ($selectedUserId <= 0 && $selectedUsername !== '') {
            $selectedUser = $this->users->findByUsername($selectedUsername);
            $selectedUserId = (int) ($selectedUser['id'] ?? 0);
        }

        $selectedUser = null;

        if ($selectedUserId > 0 && $selectedUserId !== $currentUserId) {
            $selectedUser = $this->users->findById($selectedUserId);
        }

        if ($selectedUser === null || (int) $selectedUser['id'] === $currentUserId) {
            return [0, null, ''];
        }

        return [
            (int) $selectedUser['id'],
            $selectedUser,
            (string) $selectedUser['username'],
        ];
    }

    private function ensureSelectedConversationListed(array $conversations, ?array $selectedUser, array $thread): array
    {
        if ($selectedUser === null) {
            return $conversations;
        }

        $selectedUserId = (int) $selectedUser['id'];

        foreach ($conversations as $conversation) {
            if ((int) ($conversation['other_user_id'] ?? 0) === $selectedUserId) {
                return $conversations;
            }
        }

        $lastMessage = $thread[array_key_last($thread)] ?? null;

        array_unshift($conversations, [
            'other_user_id' => $selectedUserId,
            'username' => (string) $selectedUser['username'],
            'last_message_at' => (string) ($lastMessage['created_at'] ?? ''),
            'unread_count' => 0,
        ]);

        return $conversations;
    }

    private function threadResponsePayload(
        int $currentUserId,
        array $conversations,
        ?array $selectedUser,
        array $thread
    ): array {
        $lastMessage = $thread[array_key_last($thread)] ?? null;
        $selectedUserId = (int) ($selectedUser['id'] ?? 0);

        return [
            'ok' => true,
            'conversation' => $selectedUser !== null
                ? $this->conversationPayload(
                    $selectedUser,
                    (string) ($lastMessage['created_at'] ?? ''),
                    0
                )
                : null,
            'conversations' => $this->serializeConversations($conversations),
            'thread' => array_map(
                fn(array $item): array => $this->messagePayload($item, $currentUserId),
                $thread
            ),
            'lastReadMessageId' => $selectedUserId > 0
                ? $this->messages->getLastReadOwnMessageId($currentUserId, $selectedUserId)
                : 0,
        ];
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

        [$selectedUserId, $selectedUser, $selectedUsername] = $this->resolveSelectedConversation($request, $userId);

        $thread = [];
        if ($selectedUser !== null) {
            $this->messages->markConversationRead($userId, $selectedUserId);
            $thread = $this->messages->getConversationMessages($userId, $selectedUserId);
        }

        // Liste calculée après marquage comme lu pour éviter un compteur obsolète.
        $conversations = $this->messages->getConversations($userId);
        $conversations = $this->ensureSelectedConversationListed($conversations, $selectedUser, $thread);

        // 5) Messages flash (succès / erreurs), affichés une seule fois
        $errors = $_SESSION['errors_messages'] ?? [];
        $success = $_SESSION['success_messages'] ?? '';

        unset($_SESSION['errors_messages'], $_SESSION['success_messages']);

        // 6) Affichage du template de messagerie
        require dirname(__DIR__, 2) . '/templates/messages/index.php';
    }

    public function thread(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response, $request);
        if ($userId === null) {
            return;
        }

        [$selectedUserId, $selectedUser] = $this->resolveSelectedConversation($request, $userId);

        if ($selectedUser === null) {
            $response->json([
                'ok' => false,
                'message' => $this->hasConversationSelection($request)
                    ? 'Conversation introuvable.'
                    : 'Aucune conversation sélectionnée.',
            ], $this->hasConversationSelection($request) ? 404 : 422);
            return;
        }

        $this->messages->markConversationRead($userId, $selectedUserId);
        $thread = $this->messages->getConversationMessages($userId, $selectedUserId);
        $conversations = $this->messages->getConversations($userId);
        $conversations = $this->ensureSelectedConversationListed($conversations, $selectedUser, $thread);

        $response->json($this->threadResponsePayload($userId, $conversations, $selectedUser, $thread));
    }

    public function poll(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response, $request);
        if ($userId === null) {
            return;
        }

        [$selectedUserId, $selectedUser] = $this->resolveSelectedConversation($request, $userId);

        if ($selectedUser === null) {
            $response->json([
                'ok' => false,
                'message' => 'Conversation introuvable.',
            ], 404);
            return;
        }

        $afterId = max(0, (int) $request->input('after_id', 0));
        $newMessages = $this->messages->getConversationMessagesAfter($userId, $selectedUserId, $afterId);

        // Toute nouvelle entrée reçue depuis l'interlocuteur devient lue dès que le fil est actif.
        $this->messages->markConversationRead($userId, $selectedUserId);
        $conversations = $this->messages->getConversations($userId);
        $conversations = $this->ensureSelectedConversationListed($conversations, $selectedUser, []);

        $response->json([
            'ok' => true,
            'messages' => array_map(
                fn(array $item): array => $this->messagePayload($item, $userId),
                $newMessages
            ),
            'conversations' => $this->serializeConversations($conversations),
            'lastReadMessageId' => $this->messages->getLastReadOwnMessageId($userId, $selectedUserId),
        ]);
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
        $userId = $this->requireAuth($response, $request);
        if ($userId === null) {
            return;
        }

        // 2) Récupération des inputs
        $receiverId = (int) $request->input('receiver_id', 0);
        $receiverUsername = trim((string) $request->input('receiver_username', ''));
        $content = trim((string) $request->input('content', ''));

        $receiver = null;

        if ($receiverId <= 0 && $receiverUsername !== '') {
            $receiver = $this->users->findByUsername($receiverUsername);
            $receiverId = (int) ($receiver['id'] ?? 0);
        }

        if ($receiver === null && $receiverId > 0) {
            $receiver = $this->users->findById($receiverId);
        }

        // 3) Validation destinataire
        // - id valide
        // - on ne s'envoie pas de message à soi-même
        if ($receiver === null || (int) $receiver['id'] !== $receiverId || $receiverId === $userId) {
            $this->respondSendError($request, $response, 'Destinataire introuvable ou invalide.');
            return;
        }

        // 4) Validation contenu message
        if ($this->contentLength($content) < 1) {
            $this->respondSendError($request, $response, 'Le message ne peut pas être vide.', $receiverId);
            return;
        }

        // 5) Envoi en DB
        $message = $this->messages->create($userId, $receiverId, $content);

        if ($message === null) {
            $this->respondSendError($request, $response, 'Envoi impossible.', $receiverId, 500);
            return;
        }

        // 6) Notification au destinataire (message reçu)
        $this->notifications->create(
            $receiverId, // destinataire de la notification
            $userId,     // acteur (celui qui a envoyé le message)
            'message',
            null,        // pas d'entity_id utilisé ici (possible plus tard)
            'Vous avez reçu un nouveau message.'
        );

        if ($request->expectsJson()) {
            $response->json([
                'ok' => true,
                'message' => 'Message envoyé.',
                'conversation' => $this->conversationPayload($receiver, (string) $message['created_at'], 0),
                'messageItem' => $this->messagePayload($message, $userId),
                'thread' => array_map(
                    fn(array $item): array => $this->messagePayload($item, $userId),
                    $this->messages->getConversationMessages($userId, $receiverId)
                ),
                'lastReadMessageId' => $this->messages->getLastReadOwnMessageId($userId, $receiverId),
            ]);
            return;
        }

        // 7) Message flash succès + retour sur la conversation
        $_SESSION['success_messages'] = 'Message envoyé.';
        $this->redirectToConversation($response, $receiverId);
    }
}
