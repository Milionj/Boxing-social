<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Chargement manuel du .env, récupérer la config (DB, etc.).
 */
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($key)] = trim($value);
    }
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Database\Database;
use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\PostController;
use App\Controllers\FriendshipController;
use App\Controllers\NotificationController;


session_start();

$request = new Request();
$response = new Response();

try {
    $router = new Router();
    $router->get('/register', fn() => (new AuthController())->showRegister($response));
    $router->post('/register', fn() => (new AuthController())->register($request, $response));

    $router->get('/login', fn() => (new AuthController())->showLogin($response));
    $router->post('/login', fn() => (new AuthController())->login($request, $response));

    $router->get('/profile', fn() => (new ProfileController())->show($response));
    $router->post('/profile', fn() => (new ProfileController())->update($request, $response));

    $router->post('/logout', fn() => (new AuthController())->logout($response));
    $router->post('/profile/password', fn() => (new ProfileController())->updatePassword($request, $response));

    $router->post('/profile/avatar', fn() => (new ProfileController())->updateAvatar($response));

    // Posts
    $router->get('/posts', fn() => (new PostController())->index($response));
    $router->get('/posts/create', fn() => (new PostController())->createForm($response));
    $router->post('/posts', fn() => (new PostController())->store($request, $response));

    $router->get('/posts/edit', fn() => (new PostController())->editForm($request, $response));
    $router->post('/posts/update', fn() => (new PostController())->update($request, $response));
    $router->post('/posts/delete', fn() => (new PostController())->delete($request, $response));

    // Comments
    $router->post('/comments', fn() => (new PostController())->addComment($request, $response));
    $router->post('/comments/delete', fn() => (new PostController())->deleteComment($request, $response));

    $router->post('/likes/toggle', fn() => (new PostController())->toggleLike($request, $response));

    // Friendships
    $router->get('/friends', fn() => (new FriendshipController())->index($response));
    $router->post('/friends/send', fn() => (new FriendshipController())->send($request, $response));
    $router->post('/friends/accept', fn() => (new FriendshipController())->accept($request, $response));
    $router->post('/friends/decline', fn() => (new FriendshipController())->decline($request, $response));

    // Notifications
    $router->get('/notifications', fn() => (new NotificationController())->index($response));
    $router->post('/notifications/read', fn() => (new NotificationController())->markRead($request, $response));
    $router->post('/notifications/read-all', fn() => (new NotificationController())->markAllRead($response));

    $router->get('/', function () use ($response): void {
        $user = $_SESSION['user']['username'] ?? null;
        if ($user !== null) {
            $response->html('<h1>Boxing Social</h1><p>Connecte: ' . htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . '</p>');
            return;
        }

        $response->html('<h1>Boxing Social</h1><p>Base app OK</p><p><a href="/login">Connexion</a> | <a href="/register">Inscription</a></p>');
    });

    $router->get('/health', function () use ($response): void {
        $pdo = Database::getConnection();
        $pdo->query('SELECT 1');
        $response->json([
            'status' => 'ok',
            'db' => 'connected',
        ]);
    });

    $router->dispatch($request, $response);
} catch (Throwable $e) {
    if (($_ENV['APP_DEBUG'] ?? '0') === '1') {
        error_log($e->getMessage());
    }
    $response->errorPage(500, '500');
}
