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
    private const USERS_PER_PAGE = 12;
    private const POSTS_PER_PAGE = 10;
    private const COMMENTS_PER_PAGE = 10;
    private const LOGS_PER_PAGE = 12;

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

    private function sanitizeRedirect(string $redirectTo): string
    {
        if ($redirectTo === '' || !str_starts_with($redirectTo, '/')) {
            return '/admin';
        }

        return $redirectTo;
    }

    private function redirectBack(Request $request, Response $response): void
    {
        $response->redirect($this->sanitizeRedirect((string) $request->input('redirect_to', '/admin')));
    }

    private function positiveInt(mixed $value, int $default = 1): int
    {
        $int = (int) $value;
        return $int > 0 ? $int : $default;
    }

    private function paginate(int $total, int $page, int $perPage): array
    {
        $totalPages = max(1, (int) ceil($total / $perPage));
        $currentPage = min(max(1, $page), $totalPages);

        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'offset' => ($currentPage - 1) * $perPage,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $logs
     * @return array<int, array<string, mixed>>
     */
    private function decorateLogs(array $logs): array
    {
        return array_map(function (array $log): array {
            $details = null;
            if (is_string($log['details'] ?? null) && $log['details'] !== '') {
                $decoded = json_decode((string) $log['details'], true);
                if (is_array($decoded)) {
                    $details = $decoded;
                }
            }

            $log['details_map'] = $details;

            return $log;
        }, $logs);
    }

    public function index(Request $request, Response $response): void
    {
        $adminId = $this->requireAdmin($response);
        if ($adminId === null) {
            return;
        }

        $usersFilters = [
            'query' => trim((string) $request->input('users_q', '')),
            'role' => trim((string) $request->input('users_role', '')),
            'status' => trim((string) $request->input('users_status', '')),
        ];
        $postsFilters = [
            'query' => trim((string) $request->input('posts_q', '')),
            'visibility' => trim((string) $request->input('posts_visibility', '')),
            'post_type' => trim((string) $request->input('posts_type', '')),
        ];
        $commentsFilters = [
            'query' => trim((string) $request->input('comments_q', '')),
            'post_id' => $this->positiveInt($request->input('comments_post_id', 0), 0),
        ];
        $logsFilters = [
            'query' => trim((string) $request->input('logs_q', '')),
            'action' => trim((string) $request->input('logs_action', '')),
            'target_type' => trim((string) $request->input('logs_target_type', '')),
        ];

        $usersPagination = $this->paginate(
            $this->users->countForAdmin($usersFilters),
            $this->positiveInt($request->input('users_page', 1)),
            self::USERS_PER_PAGE
        );
        $postsPagination = $this->paginate(
            $this->posts->countForAdmin($postsFilters),
            $this->positiveInt($request->input('posts_page', 1)),
            self::POSTS_PER_PAGE
        );
        $commentsPagination = $this->paginate(
            $this->comments->countForAdmin($commentsFilters),
            $this->positiveInt($request->input('comments_page', 1)),
            self::COMMENTS_PER_PAGE
        );
        $logsPagination = $this->paginate(
            $this->logs->count($logsFilters),
            $this->positiveInt($request->input('logs_page', 1)),
            self::LOGS_PER_PAGE
        );

        $users = $this->users->searchForAdmin(
            $usersFilters,
            self::USERS_PER_PAGE,
            $usersPagination['offset']
        );
        $posts = $this->posts->searchForAdmin(
            $postsFilters,
            self::POSTS_PER_PAGE,
            $postsPagination['offset']
        );
        $comments = $this->comments->searchForAdmin(
            $commentsFilters,
            self::COMMENTS_PER_PAGE,
            $commentsPagination['offset']
        );
        $logs = $this->decorateLogs(
            $this->logs->search(
                $logsFilters,
                self::LOGS_PER_PAGE,
                $logsPagination['offset']
            )
        );

        $stats = [
            'users' => $this->users->countForAdmin(),
            'posts' => $this->posts->countForAdmin(),
            'comments' => $this->comments->countForAdmin(),
            'logs' => $this->logs->count(),
        ];

        $currentRequestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/admin');

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
            $this->redirectBack($request, $response);
            return;
        }

        if ($userId === $adminId) {
            $_SESSION['errors_admin'] = ['Action refusée : impossible de désactiver votre propre compte admin.'];
            $this->redirectBack($request, $response);
            return;
        }

        $targetUser = $this->users->findById($userId);
        if ($targetUser === null) {
            $_SESSION['errors_admin'] = ['Utilisateur invalide.'];
            $this->redirectBack($request, $response);
            return;
        }

        $ok = $this->users->setActiveByAdmin($userId, $isActive);
        if (!$ok) {
            $_SESSION['errors_admin'] = ['Mise à jour utilisateur impossible.'];
            $this->redirectBack($request, $response);
            return;
        }

        $this->logs->create(
            $adminId,
            $isActive ? 'user_activated' : 'user_disabled',
            'user',
            $userId,
            json_encode([
                'username' => (string) $targetUser['username'],
                'email' => (string) $targetUser['email'],
                'is_active' => $isActive,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $_SESSION['success_admin'] = $isActive ? 'Utilisateur activé.' : 'Utilisateur désactivé.';
        $this->redirectBack($request, $response);
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
            $this->redirectBack($request, $response);
            return;
        }

        $post = $this->posts->findDetailedById($postId);
        if ($post === null) {
            $_SESSION['errors_admin'] = ['Post invalide.'];
            $this->redirectBack($request, $response);
            return;
        }

        $ok = $this->posts->deleteByAdmin($postId);
        if (!$ok) {
            $_SESSION['errors_admin'] = ['Suppression du post impossible.'];
            $this->redirectBack($request, $response);
            return;
        }

        $this->logs->create(
            $adminId,
            'post_deleted',
            'post',
            $postId,
            json_encode([
                'username' => (string) $post['username'],
                'title' => (string) ($post['title'] ?? ''),
                'visibility' => (string) $post['visibility'],
                'post_type' => (string) $post['post_type'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        $_SESSION['success_admin'] = 'Post supprimé.';
        $this->redirectBack($request, $response);
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
            $this->redirectBack($request, $response);
            return;
        }

        $comment = $this->comments->findById($commentId);
        if ($comment === null) {
            $_SESSION['errors_admin'] = ['Commentaire invalide.'];
            $this->redirectBack($request, $response);
            return;
        }

        $ok = $this->comments->deleteByAdmin($commentId);
        if (!$ok) {
            $_SESSION['errors_admin'] = ['Suppression du commentaire impossible.'];
            $this->redirectBack($request, $response);
            return;
        }

        $this->logs->create(
            $adminId,
            'comment_deleted',
            'comment',
            $commentId,
            json_encode([
                'username' => (string) $comment['username'],
                'post_id' => (int) $comment['post_id'],
                'content' => (string) $comment['content'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        $_SESSION['success_admin'] = 'Commentaire supprimé.';
        $this->redirectBack($request, $response);
    }
}
