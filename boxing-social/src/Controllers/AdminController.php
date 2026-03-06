<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\AdminLog;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

final class AdminController
{
    private User $users;
    private Post $posts;
    private Comment $comments;
    private AdminLog $logs;

    public function __construct()
    {
        $this->users = new User();
        $this->posts = new Post();
        $this->comments = new Comment();
        $this->logs = new AdminLog();
    }

    private function requireAdmin(Response $response): ?int
    {
        $id = $_SESSION['user']['id'] ?? null;
        $role = $_SESSION['user']['role'] ?? null;

        if (!is_int($id)) {
            $response->redirect('/login');
            return null;
        }

        if ($role !== 'admin') {
            $response->errorPage(404, '404');
            return null;
        }

        return $id;
    }

    public function index(Response $response): void
    {
        $adminId = $this->requireAdmin($response);
        if ($adminId === null) {
            return;
        }

        $users = $this->users->latestUsers(60);
        $posts = $this->posts->latestForAdmin(80);
        $comments = $this->comments->latestForAdmin(100);
        $logs = $this->logs->latest(100);

        $errors = $_SESSION['errors_admin'] ?? [];
        $success = $_SESSION['success_admin'] ?? '';
        unset($_SESSION['errors_admin'], $_SESSION['success_admin']);

        require dirname(__DIR__, 2) . '/templates/admin/index.php';
    }

    public function toggleUser(Request $request, Response $response): void
    {
        $adminId = $this->requireAdmin($response);
        if ($adminId === null) {
            return;
        }

        $userId = (int) $request->input('user_id', 0);
        $isActive = (int) $request->input('is_active', 1) === 1;

        if ($userId <= 0) {
            $_SESSION['errors_admin'] = ['Utilisateur invalide.'];
            $response->redirect('/admin');
            return;
        }

        if ($userId === $adminId) {
            $_SESSION['errors_admin'] = ['Action refusee: impossible de desactiver votre propre compte admin.'];
            $response->redirect('/admin');
            return;
        }

        $ok = $this->users->setActiveByAdmin($userId, $isActive);
        if (!$ok) {
            $_SESSION['errors_admin'] = ['Mise a jour utilisateur impossible.'];
            $response->redirect('/admin');
            return;
        }

        $this->logs->create(
            $adminId,
            $isActive ? 'user_activated' : 'user_disabled',
            'user',
            $userId,
            json_encode(['is_active' => $isActive], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $_SESSION['success_admin'] = $isActive ? 'Utilisateur active.' : 'Utilisateur desactive.';
        $response->redirect('/admin');
    }

    public function deletePost(Request $request, Response $response): void
    {
        $adminId = $this->requireAdmin($response);
        if ($adminId === null) {
            return;
        }

        $postId = (int) $request->input('post_id', 0);
        if ($postId <= 0) {
            $_SESSION['errors_admin'] = ['Post invalide.'];
            $response->redirect('/admin');
            return;
        }

        $ok = $this->posts->deleteByAdmin($postId);
        if (!$ok) {
            $_SESSION['errors_admin'] = ['Suppression du post impossible.'];
            $response->redirect('/admin');
            return;
        }

        $this->logs->create($adminId, 'post_deleted', 'post', $postId, null);
        $_SESSION['success_admin'] = 'Post supprime.';
        $response->redirect('/admin');
    }

    public function deleteComment(Request $request, Response $response): void
    {
        $adminId = $this->requireAdmin($response);
        if ($adminId === null) {
            return;
        }

        $commentId = (int) $request->input('comment_id', 0);
        if ($commentId <= 0) {
            $_SESSION['errors_admin'] = ['Commentaire invalide.'];
            $response->redirect('/admin');
            return;
        }

        $ok = $this->comments->deleteByAdmin($commentId);
        if (!$ok) {
            $_SESSION['errors_admin'] = ['Suppression du commentaire impossible.'];
            $response->redirect('/admin');
            return;
        }

        $this->logs->create($adminId, 'comment_deleted', 'comment', $commentId, null);
        $_SESSION['success_admin'] = 'Commentaire supprime.';
        $response->redirect('/admin');
    }
}
