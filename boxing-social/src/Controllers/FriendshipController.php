<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Friendship;
use App\Models\Notification;
use App\Models\User;
/**
 * FriendshipController
 * --------------------
 * Gère les actions liées au système d'amis :
 * - afficher la page "amis" (demandes reçues, envoyées, amis)
 * - envoyer une demande
 * - accepter une demande
 * - refuser une demande
 *
 * Le controller :
 * - vérifie l'authentification
 * - valide les inputs (IDs, etc.)
 * - appelle le modèle Friendship (requêtes SQL)
 * - gère les messages flash en session + redirections
 */
final class FriendshipController
{
    /**
     * Modèle Friendship (accès DB pour la table friendships)
     */
    private Friendship $friendships;
    private Notification $notifications;
    private User $users;

    public function __construct()
    {
        // Instancie le modèle (qui récupère la connexion PDO)
        $this->friendships = new Friendship();
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
     * GET /friends
     * Affiche la page "amis" avec :
     * - demandes entrantes
     * - demandes sortantes
     * - liste des amis
     * + messages flash (errors/success)
     */
    public function index(Response $response): void
    {
        // Auth obligatoire
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        // Récupération des 3 blocs de données pour la page contacts/amis
        $incoming = $this->friendships->incomingRequests($userId); // demandes reçues
        $outgoing = $this->friendships->outgoingRequests($userId); // demandes envoyées
        $friends = $this->friendships->friendsOf($userId);         // amis acceptés

        // Messages flash (affichés une seule fois)
        $errors = $_SESSION['errors_friends'] ?? [];
        $success = $_SESSION['success_friends'] ?? '';

        // On vide la session après lecture (pattern flash)
        unset($_SESSION['errors_friends'], $_SESSION['success_friends']);

        // Affiche le template de la page amis
        require dirname(__DIR__, 2) . '/templates/friends/index.php';
    }

    /**
     * POST /friends/send
     * Envoie une demande d'ami à un autre utilisateur.
     */
    public function send(Request $request, Response $response): void
    {
        // Auth obligatoire
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        // On laisse l'utilisateur chercher par pseudo, puis on résout le compte en base.
        $targetUsername = trim((string) $request->input('username', ''));
        $targetUser = $targetUsername !== '' ? $this->users->findByUsername($targetUsername) : null;
        $targetId = (int) ($targetUser['id'] ?? 0);

        // Validation minimale :
        // - ID valide
        // - on ne peut pas s'ajouter soi-même
        if ($targetId <= 0 || $targetId === $userId) {
            $_SESSION['errors_friends'] = ['Pseudo introuvable ou demande invalide.'];
            $response->redirect('/friends');
            return;
        }

        // Envoi de la demande (INSERT status = pending)
        $ok = $this->friendships->sendRequest($userId, $targetId);

        // Si échec (ex: doublon, contrainte SQL, etc.)
        if (!$ok) {
            $_SESSION['errors_friends'] = ['Impossible d’envoyer la demande (déjà existante ?)'];
            $response->redirect('/friends');
            return;
        }

        $this->notifications->create(
            $targetId,
            $userId,
            'friend_request',
            null,
            'Vous avez reçu une demande d’ami.'
        );

        // Message flash succès + redirect
        $_SESSION['success_friends'] = 'Demande envoyée.';
        $response->redirect('/friends');
    }

    /**
     * POST /friends/accept
     * Le destinataire accepte une demande d'ami.
     */
    public function accept(Request $request, Response $response): void
    {
        // Auth obligatoire
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        // ID de la ligne friendships à traiter
        $id = (int) $request->input('friendship_id', 0);

        if ($id <= 0) {
            $_SESSION['errors_friends'] = ['Demande introuvable.'];
            $response->redirect('/friends');
            return;
        }

        $incoming = $this->friendships->incomingRequests($userId);
        $actorId = null;
        foreach ($incoming as $req) {
            if ((int) $req['id'] === $id) {
                $actorId = (int) $req['requester_id'];
                break;
            }
        }

        // Le modèle vérifie que :
        // - l'utilisateur connecté est bien le destinataire
        // - la demande est en "pending"
        // - le status demandé est autorisé ("accepted")
        $this->friendships->updateStatusByAddressee($id, $userId, 'accepted');

        if ($actorId !== null) {
            $this->notifications->create(
                $actorId,
                $userId,
                'friend_accept',
                null,
                'Votre demande d’ami a été acceptée.'
            );
        }

        $_SESSION['success_friends'] = 'Demande acceptée.';
        $response->redirect('/friends');
    }

    /**
     * POST /friends/decline
     * Le destinataire refuse une demande d'ami.
     */
    public function decline(Request $request, Response $response): void
    {
        // Auth obligatoire
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        // ID de la demande d'amitié
        $id = (int) $request->input('friendship_id', 0);

        if ($id <= 0) {
            $_SESSION['errors_friends'] = ['Demande introuvable.'];
            $response->redirect('/friends');
            return;
        }

        // Le modèle applique la mise à jour conditionnelle vers "declined"
        $this->friendships->updateStatusByAddressee($id, $userId, 'declined');

        $_SESSION['success_friends'] = 'Demande refusée.';
        $response->redirect('/friends');
    }
}
