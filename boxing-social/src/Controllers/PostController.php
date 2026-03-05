<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Post;
use App\Models\Comment;

final class PostController
{
    private Post $posts;
    private Comment $comments;

    public function __construct()
    {
        $this->posts = new Post();
        $this->comments = new Comment();
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

    public function index(Response $response): void
    {
        $feed = $this->posts->latestFeed(30);
        $commentsByPost = [];
        foreach ($feed as $post) {
            $postId = (int) $post['id'];
            $commentsByPost[$postId] = $this->comments->byPostId($postId);
        }

        require dirname(__DIR__, 2) . '/templates/posts/index.php';
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
        $response->redirect('/posts');
        return;
    }

    $post = $this->posts->findById($postId);
    if ($post === null) {
        $_SESSION['errors_comments'] = ['Post introuvable.'];
        $response->redirect('/posts');
        return;
    }

    $this->comments->create($postId, $userId, $content);
    $_SESSION['success_comments'] = 'Commentaire ajoute.';
    $response->redirect('/posts');
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

    $response->redirect('/posts');
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
