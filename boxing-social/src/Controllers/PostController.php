<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Post;

final class PostController
{
    private Post $posts;

    public function __construct()
    {
        $this->posts = new Post();
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
        require dirname(__DIR__, 2) . '/templates/posts/index.php';
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
}
