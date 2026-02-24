<?php
declare(strict_types=1);

/* <!-- le fichier fait:
active le mode strict + erreurs en dev
charge .env à la main (variables de config)
charge l’autoload Composer
instancie Request/Response/Router
déclare 2 routes de test (/ et /health)
lance le routeur (dispatch) pour servir la requête -->
 */

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

/* Charger l’autoload de Composer pour que tes classes se chargent automatiquement.
Sans ça, PHP ne sait pas où trouver :
App\Core\Router
App\Database\Database
etc. */
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Database\Database;

/* instanciation des objets principaux */
$request = new Request();
$response = new Response();
$router = new Router();


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
        'db' => 'connected'
    ]);
    });

    $router->dispatch($request, $response);