<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\Notification;

final class PostController
{
    private const MIN_CONTENT_LENGTH = 5;

    private Post $posts;
    private Comment $comments;
    private Friendship $friendships;
    private Message $messages;
    private Notification $notifications;

    public function __construct()
    {
        $this->posts = new Post();
        $this->comments = new Comment();
        $this->friendships = new Friendship();
        $this->messages = new Message();
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

    private function buildTrainingInterestMessage(array $post, string $username): string
    {
        // Message automatique envoyé à l'annonceur quand quelqu'un
        // manifeste son intérêt sur une séance d'entraînement.
        $title = trim((string) ($post['title'] ?? ''));
        $sessionLabel = $title !== '' ? $title : 'séance sans titre';

        return sprintf(
            '%s a manifesté son intérêt pour votre séance "%s".',
            $username,
            $sessionLabel
        );
    }

    private function normalizeMediaSize(string $mediaSize): string
    {
        return in_array($mediaSize, ['compact', 'standard', 'large'], true) ? $mediaSize : 'standard';
    }

    private function contentLength(string $content): int
    {
        return function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
    }

    private function parseIniSizeToBytes(string $value): int
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return 0;
        }

        $unit = strtolower(substr($normalized, -1));
        $amount = (float) $normalized;

        return match ($unit) {
            'g' => (int) ($amount * 1024 * 1024 * 1024),
            'm' => (int) ($amount * 1024 * 1024),
            'k' => (int) ($amount * 1024),
            default => (int) $amount,
        };
    }

    private function requestExceedsPostMaxSize(): bool
    {
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        $postMaxSize = $this->parseIniSizeToBytes((string) ini_get('post_max_size'));

        return $contentLength > 0 && $postMaxSize > 0 && $contentLength > $postMaxSize;
    }

    private function editRedirectTarget(Request $request, int $fallbackPostId = 0): string
    {
        if ($fallbackPostId > 0) {
            return '/posts/edit?id=' . $fallbackPostId;
        }

        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $path = (string) (parse_url($referer, PHP_URL_PATH) ?? '');
        $query = (string) (parse_url($referer, PHP_URL_QUERY) ?? '');

        if ($path === '/posts/edit' && $query !== '') {
            parse_str($query, $params);
            $refererPostId = isset($params['id']) ? (int) $params['id'] : 0;
            if ($refererPostId > 0) {
                return '/posts/edit?id=' . $refererPostId;
            }
        }

        $requestPostId = (int) $request->input('id', 0);
        if ($requestPostId > 0) {
            return '/posts/edit?id=' . $requestPostId;
        }

        return '/posts';
    }

    private function extractPostFormData(Request $request): array
    {
        $postType = (string) $request->input('post_type', 'publication');
        $visibility = (string) $request->input('visibility', 'public');

        return [
            'post_type' => in_array($postType, ['publication', 'entrainement'], true) ? $postType : 'publication',
            'title' => trim((string) $request->input('title', '')),
            'content' => trim((string) $request->input('content', '')),
            'location' => trim((string) $request->input('location', '')),
            'visibility' => in_array($visibility, ['public', 'friends', 'private'], true) ? $visibility : 'public',
            'scheduled_at' => trim((string) $request->input('scheduled_at', '')),
            'media_size' => $this->normalizeMediaSize((string) $request->input('media_size', 'standard')),
            'remove_media' => (string) $request->input('remove_media', '') === '1',
        ];
    }

    private function deleteLocalPostMedia(?string $mediaPath): void
    {
        if ($mediaPath === null || $mediaPath === '' || !str_starts_with($mediaPath, '/uploads/posts/')) {
            return;
        }

        $publicDir = realpath(dirname(__DIR__, 2) . '/public');
        $uploadsDir = realpath(dirname(__DIR__, 2) . '/public/uploads/posts');
        if ($publicDir === false || $uploadsDir === false) {
            return;
        }

        $absolutePath = realpath($publicDir . $mediaPath);
        if ($absolutePath === false || !is_file($absolutePath)) {
            return;
        }

        if (!str_starts_with($absolutePath, $uploadsDir . DIRECTORY_SEPARATOR)) {
            return;
        }

        @unlink($absolutePath);
    }

    private function handleUploadedMedia(int $userId, array &$errors): array
    {
        if (
            !isset($_FILES['post_media'])
            || !is_array($_FILES['post_media'])
            || ($_FILES['post_media']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
        ) {
            return ['provided' => false, 'path' => null, 'type' => 'image'];
        }

        $file = $_FILES['post_media'];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_INI_SIZE) {
            $errors[] = sprintf(
                'Le fichier dépasse la limite serveur actuelle (%s max par fichier).',
                (string) ini_get('upload_max_filesize')
            );
            return ['provided' => true, 'path' => null, 'type' => 'image'];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors de l’envoi du média.';
            return ['provided' => true, 'path' => null, 'type' => 'image'];
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        $mime = mime_content_type($tmpPath) ?: '';
        $size = (int) ($file['size'] ?? 0);

        $allowedMimes = [
            'image/jpeg' => ['ext' => 'jpg', 'type' => 'image', 'max' => 8 * 1024 * 1024],
            'image/png' => ['ext' => 'png', 'type' => 'image', 'max' => 8 * 1024 * 1024],
            'image/webp' => ['ext' => 'webp', 'type' => 'image', 'max' => 8 * 1024 * 1024],
            'image/gif' => ['ext' => 'gif', 'type' => 'gif', 'max' => 12 * 1024 * 1024],
            'video/mp4' => ['ext' => 'mp4', 'type' => 'video', 'max' => 25 * 1024 * 1024],
            'video/webm' => ['ext' => 'webm', 'type' => 'video', 'max' => 25 * 1024 * 1024],
        ];

        if (!isset($allowedMimes[$mime])) {
            $errors[] = 'Format non autorisé. Utilisez JPG, PNG, WEBP, GIF, MP4 ou WEBM.';
            return ['provided' => true, 'path' => null, 'type' => 'image'];
        }

        if ($size > $allowedMimes[$mime]['max']) {
            $errors[] = $allowedMimes[$mime]['type'] === 'video'
                ? 'Vidéo trop volumineuse (max 25 Mo).'
                : 'Média trop volumineux.';
            return ['provided' => true, 'path' => null, 'type' => 'image'];
        }

        $ext = $allowedMimes[$mime]['ext'];
        $type = $allowedMimes[$mime]['type'];
        $name = 'post_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/posts';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            $errors[] = 'Impossible de créer le dossier des médias.';
            return ['provided' => true, 'path' => null, 'type' => 'image'];
        }

        $target = $uploadDir . '/' . $name;
        if (!move_uploaded_file($tmpPath, $target)) {
            $errors[] = 'Impossible de déplacer le média.';
            return ['provided' => true, 'path' => null, 'type' => 'image'];
        }

        return [
            'provided' => true,
            'path' => '/uploads/posts/' . $name,
            'type' => $type,
        ];
    }

    public function index(Request $request, Response $response): void
    {
        $currentUserId = $this->requireAuth($response);
        if ($currentUserId === null) {
            return;
        }

        $perPage = 8;
        $currentPage = max(1, (int) $request->input('page', 1));
        $totalPosts = $this->posts->countByUserId($currentUserId);
        $totalPages = max(1, (int) ceil($totalPosts / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $feed = $this->posts->latestByUserId($currentUserId, $perPage, $offset);
        $commentsByPost = [];
        foreach ($feed as $post) {
            $postId = (int) $post['id'];
            $commentsByPost[$postId] = $this->comments->byPostId($postId);
        }

        $likesCountByPost = [];
        $likedByCurrentUser = [];
        $interestCountByPost = [];
        $interestedByCurrentUser = [];

        $quickFriends = [];
        foreach ($feed as $post) {
            $postId = (int) $post['id'];
            $likesCountByPost[$postId] = $this->posts->likesCountByPostId($postId);
            $interestCountByPost[$postId] = $this->posts->interestCountByPostId($postId);

            if (is_int($currentUserId)) {
                $likedByCurrentUser[$postId] = $this->posts->isLikedByUser($postId, $currentUserId);
                $interestedByCurrentUser[$postId] = $this->posts->hasInterestByUser($postId, $currentUserId);
            } else {
                $likedByCurrentUser[$postId] = false;
                $interestedByCurrentUser[$postId] = false;
            }
        }

        $quickFriends = array_slice($this->friendships->friendsOf($currentUserId), 0, 10);

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
        $interestCount = $this->posts->interestCountByPostId($postId);

        $currentUserId = $_SESSION['user']['id'] ?? null;
        $isLiked = is_int($currentUserId) ? $this->posts->isLikedByUser($postId, $currentUserId) : false;
        $isInterested = is_int($currentUserId) ? $this->posts->hasInterestByUser($postId, $currentUserId) : false;

        $errorsComments = $_SESSION['errors_comments'] ?? [];
        $successComments = $_SESSION['success_comments'] ?? '';
        $errorsLikes = $_SESSION['errors_likes'] ?? [];
        $errorsInterest = $_SESSION['errors_posts_interest'] ?? [];
        $successInterest = $_SESSION['success_posts_interest'] ?? '';
        unset(
            $_SESSION['errors_comments'],
            $_SESSION['success_comments'],
            $_SESSION['errors_likes'],
            $_SESSION['errors_posts_interest'],
            $_SESSION['success_posts_interest']
        );

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
                'Votre post a reçu un like.'
            );
        }

        $this->redirectBack($request, $response, '/posts');
    }

    public function expressInterest(Request $request, Response $response): void
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

        $post = $this->posts->findDetailedById($postId);
        if ($post === null) {
            $_SESSION['errors_posts_interest'] = ['Séance introuvable.'];
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        if (($post['post_type'] ?? 'publication') !== 'entrainement') {
            $_SESSION['errors_posts_interest'] = ['Cette action est réservée aux déclarations de séances.'];
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        if ((int) $post['user_id'] === $userId) {
            $_SESSION['errors_posts_interest'] = ['Vous ne pouvez pas manifester votre intérêt sur votre propre séance.'];
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        if ($this->posts->hasInterestByUser($postId, $userId)) {
            $_SESSION['errors_posts_interest'] = ['Vous avez déjà manifesté votre intérêt pour cette séance.'];
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        $username = (string) ($_SESSION['user']['username'] ?? 'Un utilisateur');
        $content = $this->buildTrainingInterestMessage($post, $username);

        if (!$this->posts->addInterest($postId, $userId)) {
            $_SESSION['errors_posts_interest'] = ['Vous avez déjà manifesté votre intérêt pour cette séance.'];
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        if (!$this->messages->send($userId, (int) $post['user_id'], $content)) {
            $_SESSION['errors_posts_interest'] = ['Impossible d’envoyer votre intérêt pour le moment.'];
            $this->redirectBack($request, $response, '/posts');
            return;
        }

        $this->notifications->create(
            (int) $post['user_id'],
            $userId,
            'message',
            null,
            'Un utilisateur a manifesté son intérêt pour votre séance.'
        );

        $_SESSION['success_posts_interest'] = 'Votre intérêt a été envoyé à l’annonceur.';
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
            $_SESSION['errors_comments'] = ['Commentaire invalide (minimum 2 caractères).'];
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
        $formData = $_SESSION['old_posts'] ?? [
            'post_type' => 'publication',
            'title' => '',
            'content' => '',
            'location' => '',
            'visibility' => 'public',
            'scheduled_at' => '',
            'media_size' => 'standard',
            'remove_media' => false,
        ];
        unset($_SESSION['errors_posts'], $_SESSION['success_posts'], $_SESSION['old_posts']);

        require dirname(__DIR__, 2) . '/templates/posts/create.php';
    }

    public function store(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        if ($this->requestExceedsPostMaxSize()) {
            $_SESSION['errors_posts'] = [
                sprintf(
                    'La vidéo dépasse la limite serveur actuelle (%s max par requête). Réduisez le fichier ou augmentez post_max_size / upload_max_filesize dans PHP.',
                    (string) ini_get('post_max_size')
                ),
            ];
            $response->redirect('/posts/create');
            return;
        }

        $formData = $this->extractPostFormData($request);
        $postType = $formData['post_type'];
        $title = $formData['title'];
        $content = $formData['content'];
        $location = $formData['location'];
        $visibility = $formData['visibility'];
        $scheduledAt = $formData['scheduled_at'];
        $mediaSize = $formData['media_size'];

        $allowedTypes = ['publication', 'entrainement'];
        $allowedVisibility = ['public', 'friends', 'private'];
        $errors = [];

        if (!in_array($postType, $allowedTypes, true)) {
            $errors[] = 'Type de post invalide.';
        }

        if ($content === '' || $this->contentLength($content) < self::MIN_CONTENT_LENGTH) {
            $errors[] = sprintf('Le contenu doit contenir au moins %d caractères.', self::MIN_CONTENT_LENGTH);
        }

        if (!in_array($visibility, $allowedVisibility, true)) {
            $errors[] = 'Visibilite invalide.';
        }

        $normalizedScheduledAt = null;
        if ($postType === 'entrainement') {
            if ($scheduledAt === '') {
                $errors[] = 'Une séance d’entraînement doit avoir une date et une heure.';
            } else {
                $timestamp = strtotime($scheduledAt);
                if ($timestamp === false) {
                    $errors[] = 'Date de séance invalide.';
                } else {
                    $normalizedScheduledAt = date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

        $uploadedMedia = $this->handleUploadedMedia($userId, $errors);
        $mediaPath = $uploadedMedia['path'];
        $mediaType = $uploadedMedia['type'];

        if ($errors !== []) {
            $_SESSION['errors_posts'] = $errors;
            $_SESSION['old_posts'] = $formData;
            $response->redirect('/posts/create');
            return;
        }

        $created = $this->posts->create($userId, $postType, $title, $content, $mediaPath, $mediaType, $mediaSize, $location, $visibility, $normalizedScheduledAt);
        if (!$created) {
            $this->deleteLocalPostMedia($mediaPath);
            $_SESSION['errors_posts'] = ['Impossible d’enregistrer le post.'];
            $_SESSION['old_posts'] = $formData;
            $response->redirect('/posts/create');
            return;
        }

        $_SESSION['success_posts'] = 'Post créé avec succès.';
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
    $old = $_SESSION['old_posts_edit'] ?? [];
    $formData = [
        'post_type' => (string) ($post['post_type'] ?? 'publication'),
        'title' => (string) ($post['title'] ?? ''),
        'content' => (string) ($post['content'] ?? ''),
        'location' => (string) ($post['location'] ?? ''),
        'visibility' => (string) ($post['visibility'] ?? 'public'),
        'scheduled_at' => !empty($post['scheduled_at']) ? date('Y-m-d\TH:i', strtotime((string) $post['scheduled_at'])) : '',
        'media_size' => (string) ($post['media_size'] ?? 'standard'),
        'remove_media' => false,
    ];
    if (is_array($old)) {
        $formData = array_merge($formData, $old);
    }

    unset($_SESSION['errors_posts_edit'], $_SESSION['old_posts_edit']);

    require dirname(__DIR__, 2) . '/templates/posts/edit.php';
}

// modification d'un post
public function update(Request $request, Response $response): void
{
    $userId = $this->requireAuth($response);
    if ($userId === null) {
        return;
    }

    if ($this->requestExceedsPostMaxSize()) {
        $_SESSION['errors_posts_edit'] = [
            sprintf(
                'La vidéo dépasse la limite serveur actuelle (%s max par requête). Réduisez le fichier ou augmentez post_max_size / upload_max_filesize dans PHP.',
                (string) ini_get('post_max_size')
            ),
        ];
        $response->redirect($this->editRedirectTarget($request));
        return;
    }

    $postId = (int) $request->input('id', 0);
    $formData = $this->extractPostFormData($request);
    $postType = $formData['post_type'];
    $title = $formData['title'];
    $content = $formData['content'];
    $location = $formData['location'];
    $visibility = $formData['visibility'];
    $scheduledAt = $formData['scheduled_at'];
    $mediaSize = $formData['media_size'];
    $removeMedia = $formData['remove_media'];

    $errors = [];
    if ($postId <= 0) {
        $errors[] = 'Post invalide.';
    }
    if (!in_array($postType, ['publication', 'entrainement'], true)) {
        $errors[] = 'Type de post invalide.';
    }
    if ($content === '' || $this->contentLength($content) < self::MIN_CONTENT_LENGTH) {
        $errors[] = sprintf('Le contenu doit contenir au moins %d caractères.', self::MIN_CONTENT_LENGTH);
    }
    if (!in_array($visibility, ['public', 'friends', 'private'], true)) {
        $errors[] = 'Visibilite invalide.';
    }

    $normalizedScheduledAt = null;
    if ($postType === 'entrainement') {
        if ($scheduledAt === '') {
            $errors[] = 'Une séance d’entraînement doit avoir une date et une heure.';
        } else {
            $timestamp = strtotime($scheduledAt);
            if ($timestamp === false) {
                $errors[] = 'Date de séance invalide.';
            } else {
                $normalizedScheduledAt = date('Y-m-d H:i:s', $timestamp);
            }
        }
    }

    $post = $this->posts->findById($postId);
    if ($post === null || (int) $post['user_id'] !== $userId) {
        $errors[] = 'Vous ne pouvez modifier que vos posts.';
    }

    if ($errors !== []) {
        $_SESSION['errors_posts_edit'] = $errors;
        $_SESSION['old_posts_edit'] = $formData;
        $response->redirect('/posts/edit?id=' . $postId);
        return;
    }

    $uploadedMedia = $this->handleUploadedMedia($userId, $errors);
    $currentMediaPath = isset($post['image_path']) ? (string) $post['image_path'] : null;
    $currentMediaType = (string) ($post['media_type'] ?? 'image');
    $hasReplacement = ($uploadedMedia['provided'] ?? false) && ($uploadedMedia['path'] ?? null) !== null;

    $mediaPath = $currentMediaPath;
    $mediaType = $currentMediaType;

    if ($removeMedia && !$hasReplacement) {
        $mediaPath = null;
        $mediaType = 'image';
    }

    if ($hasReplacement) {
        $mediaPath = $uploadedMedia['path'];
        $mediaType = $uploadedMedia['type'];
    }

    if ($errors !== []) {
        $_SESSION['errors_posts_edit'] = $errors;
        $_SESSION['old_posts_edit'] = $formData;
        $response->redirect('/posts/edit?id=' . $postId);
        return;
    }

    $updated = $this->posts->updateByOwner($postId, $userId, $postType, $title, $content, $mediaPath, $mediaType, $mediaSize, $location, $visibility, $normalizedScheduledAt);
    if (!$updated) {
        if ($hasReplacement) {
            $this->deleteLocalPostMedia($mediaPath);
        }

        $_SESSION['errors_posts_edit'] = ['Impossible de mettre à jour le post.'];
        $_SESSION['old_posts_edit'] = $formData;
        $response->redirect('/posts/edit?id=' . $postId);
        return;
    }

    if (($removeMedia && !$hasReplacement) || ($hasReplacement && $currentMediaPath !== null && $currentMediaPath !== $mediaPath)) {
        $this->deleteLocalPostMedia($currentMediaPath);
    }

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
