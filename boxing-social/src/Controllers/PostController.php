<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;

final class PostController
{
    private Post $posts;
    private Comment $comments;
    private Notification $notifications;

    public function __construct()
    {
        $this->posts = new Post();
        $this->comments = new Comment();
        $this->notifications = new Notification();
    }

    private function requireAuth(Response $response): ?int
    {
        $id = $_SESSION['user']['id'] ?? null;
        if (!is_int($id)) {
            $response->redirect('/login');
            return null;
        }
        return $id;
    }

    private function redirectBack(Request $request, Response $response, string $fallback): void
    {
        $redirectTo = (string) $request->input('redirect_to', '');
        if ($redirectTo !== '' && str_starts_with($redirectTo, '/')) {
            $response->redirect($redirectTo);
            return;
        }

        $response->redirect($fallback);
    }

    public function index(Request $request, Response $response): void
    {
        $perPage = 8;
        $currentPage = max(1, (int) $request->input('page', 1));
        $totalPosts = $this->posts->feedCount();
        $totalPages = max(1, (int) ceil($totalPosts / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $feed = $this->posts->latestFeed($perPage, $offset);
        $commentsByPost = [];
        foreach ($feed as $post) {
            $postId = (int) $post['id'];
            $commentsByPost[$postId] = $this->comments->byPostId($postId);
        }

        $likesCountByPost = [];
        $likedByCurrentUser = [];

        $currentUserId = $_SESSION['user']['id'] ?? null;
        foreach ($feed as $post) {
            $postId = (int) $post['id'];
            $likesCountByPost[$postId] = $this->posts->likesCountByPostId($postId);

            if (is_int($currentUserId)) {
                $likedByCurrentUser[$postId] = $this->posts->isLikedByUser($postId, $currentUserId);
            } else {
                $likedByCurrentUser[$postId] = false;
            }
        }

        require dirname(__DIR__, 2) . '/templates/posts/index.php';

    }

    public function show(Request $request, Response $response): void
    {
        $postId = (int) $request->input('id', 0);
        if ($postId <= 0) {
            $response->errorPage(404, '404');
            return;
        }

        $post = $this->posts->findDetailedById($postId);
        if ($post === null) {
            $response->errorPage(404, '404');
            return;
        }

        $comments = $this->comments->byPostId($postId);
        $likesCount = $this->posts->likesCountByPostId($postId);

        $currentUserId = $_SESSION['user']['id'] ?? null;
        $isLiked = is_int($currentUserId) ? $this->posts->isLikedByUser($postId, $currentUserId) : false;

        $errorsComments = $_SESSION['errors_comments'] ?? [];
        $successComments = $_SESSION['success_comments'] ?? '';
        $errorsLikes = $_SESSION['errors_likes'] ?? [];
        unset($_SESSION['errors_comments'], $_SESSION['success_comments'], $_SESSION['errors_likes']);

        require dirname(__DIR__, 2) . '/templates/posts/show.php';
    }

    public function toggleLike(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        $postId = (int) $request->input('post_id', 0);
        if ($postId <= 0) {
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        $post = $this->posts->findById($postId);
        if ($post === null) {
            $_SESSION['errors_likes'] = ['Post introuvable.'];
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        $wasLiked = $this->posts->isLikedByUser($postId, $userId);
        $this->posts->toggleLike($postId, $userId);

        // NOTIF: only create notification when a new like is added (not when removed).
        // NOTIF: never notify if user likes their own post.
        if (!$wasLiked && (int) $post['user_id'] !== $userId) {
            $this->notifications->create(
                (int) $post['user_id'],
                $userId,
                'like',
                $postId,
                'Votre post a recu un like.'
            );
        }

        $this->redirectBack($request, $response, '/posts');
    }

    public function addComment(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        $postId = (int) $request->input('post_id', 0);
        $content = trim((string) $request->input('content', ''));

        if ($postId <= 0 || strlen($content) < 2) {
            $_SESSION['errors_comments'] = ['Commentaire invalide (minimum 2 caracteres).'];
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        $post = $this->posts->findById($postId);
        if ($post === null) {
            $_SESSION['errors_comments'] = ['Post introuvable.'];
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        $this->comments->create($postId, $userId, $content);

        // NOTIF: notify post owner when someone else comments.
        if ((int) $post['user_id'] !== $userId) {
            $this->notifications->create(
                (int) $post['user_id'],
                $userId,
                'comment',
                $postId,
                'Nouveau commentaire sur votre post.'
            );
        }

        $_SESSION['success_comments'] = 'Commentaire ajoute.';
        $this->redirectBack($request, $response, '/posts');
    }

public function deleteComment(Request $request, Response $response): void
{
    $userId = $this->requireAuth($response);
    if ($userId === null) {
        return;
    }

    $commentId = (int) $request->input('comment_id', 0);
    if ($commentId > 0) {
        $this->comments->deleteByOwner($commentId, $userId);
    }

    $this->redirectBack($request, $response, '/posts');
}


    public function createForm(Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        $errors = $_SESSION['errors_posts'] ?? [];
        $success = $_SESSION['success_posts'] ?? '';
        unset($_SESSION['errors_posts'], $_SESSION['success_posts']);

        require dirname(__DIR__, 2) . '/templates/posts/create.php';
    }

    public function store(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        $title = trim((string) $request->input('title', ''));
        $content = trim((string) $request->input('content', ''));
        $location = trim((string) $request->input('location', ''));
        $visibility = (string) $request->input('visibility', 'public');

        $allowedVisibility = ['public', 'friends', 'private'];
        $errors = [];

        if ($content === '' || strlen($content) < 5) {
            $errors[] = 'Le contenu doit contenir au moins 5 caracteres.';
        }

        if (!in_array($visibility, $allowedVisibility, true)) {
            $errors[] = 'Visibilite invalide.';
        }

        $imagePath = null;

        if (isset($_FILES['post_image']) && is_array($_FILES['post_image']) && ($_FILES['post_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['post_image'];

            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $errors[] = 'Erreur upload image.';
            } else {
                $maxSize = 4 * 1024 * 1024; // 4MB
                if (($file['size'] ?? 0) > $maxSize) {
                    $errors[] = 'Image trop volumineuse (max 4MB).';
                } else {
                    $tmpPath = (string) ($file['tmp_name'] ?? '');
                    $mime = mime_content_type($tmpPath) ?: '';

                    $allowedMimes = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                    ];

                    if (!isset($allowedMimes[$mime])) {
                        $errors[] = 'Format image non autorise (JPG, PNG, WEBP).';
                    } else {
                        $ext = $allowedMimes[$mime];
                        $name = 'post_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

                        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/posts';
                        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                            $errors[] = 'Impossible de creer le dossier upload posts.';
                        } else {
                            $target = $uploadDir . '/' . $name;
                            if (!move_uploaded_file($tmpPath, $target)) {
                                $errors[] = 'Impossible de deplacer l image.';
                            } else {
                                $imagePath = '/uploads/posts/' . $name;
                            }
                        }
                    }
                }
            }
        }

        if ($errors !== []) {
            $_SESSION['errors_posts'] = $errors;
            $response->redirect('/posts/create');
            return;
        }

        $this->posts->create($userId, $title, $content, $imagePath, $location, $visibility);
        $_SESSION['success_posts'] = 'Post cree avec succes.';
        $response->redirect('/posts');
    }
    public function editForm(Request $request, Response $response): void
{
    $userId = $this->requireAuth($response);
    if ($userId === null) {
        return;
    }

    $postId = (int) $request->input('id', 0);
    if ($postId <= 0) {
        $response->errorPage(404, '404');
        return;
    }

    $post = $this->posts->findById($postId);
    if ($post === null || (int) $post['user_id'] !== $userId) {
        $response->errorPage(404, '404');
        return;
    }

    $errors = $_SESSION['errors_posts_edit'] ?? [];
    unset($_SESSION['errors_posts_edit']);

    require dirname(__DIR__, 2) . '/templates/posts/edit.php';
}

// modification d'un post
public function update(Request $request, Response $response): void
{
    $userId = $this->requireAuth($response);
    if ($userId === null) {
        return;
    }

    $postId = (int) $request->input('id', 0);
    $title = trim((string) $request->input('title', ''));
    $content = trim((string) $request->input('content', ''));
    $location = trim((string) $request->input('location', ''));
    $visibility = (string) $request->input('visibility', 'public');

    $errors = [];
    if ($postId <= 0) {
        $errors[] = 'Post invalide.';
    }
    if ($content === '' || strlen($content) < 5) {
        $errors[] = 'Le contenu doit contenir au moins 5 caracteres.';
    }
    if (!in_array($visibility, ['public', 'friends', 'private'], true)) {
        $errors[] = 'Visibilite invalide.';
    }

    $post = $this->posts->findById($postId);
    if ($post === null || (int) $post['user_id'] !== $userId) {
        $errors[] = 'Vous ne pouvez modifier que vos posts.';
    }

    if ($errors !== []) {
        $_SESSION['errors_posts_edit'] = $errors;
        $response->redirect('/posts/edit?id=' . $postId);
        return;
    }

    $this->posts->updateByOwner($postId, $userId, $title, $content, $location, $visibility);
    $response->redirect('/posts');
}

public function delete(Request $request, Response $response): void
{
    $userId = $this->requireAuth($response);
    if ($userId === null) {
        return;
    }

    $postId = (int) $request->input('id', 0);
    if ($postId <= 0) {
        $response->redirect('/posts');
        return;
    }

    $this->posts->deleteByOwner($postId, $userId);
    $response->redirect('/posts');
}

}
