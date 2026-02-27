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

session_start();

$request = new Request();
$response = new Response();
$router = new Router();
$router->get('/register', fn() => (new AuthController())->showRegister($response));
$router->post('/register', fn() => (new AuthController())->register($request, $response));

$router->get('/login', fn() => (new AuthController())->showLogin($response));
$router->post('/login', fn() => (new AuthController())->login($request, $response));

$router->post('/logout', fn() => (new AuthController())->logout($response));


/**
 * Route test app
 */

$router->get('/', function () use ($response): void {
    $response->html('<h1>Boxing Social </h1><p> Bas app OK </p>');
});

/**
 * Route test DB
 */
/* sur /health, le but est :
obtenir une connexion DB via Database::getConnection()
 */
$router->get('/health', function () use ($response): void {
    $pdo = Database::getConnection();
    $pdo->query('SELECT 1');
    $response->json([
        'status' => 'ok',
        'db' => 'connected',
    ]);
});

$router->dispatch($request, $response);
