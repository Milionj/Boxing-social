<?php
declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
/**
 * Database : gère la connexion PDO à la base de données.
 * - Utilise un "singleton" : une seule connexion partagée
 * - Centralise la configuration PDO (sécurité + confort)
 */
final class Database
{
    private static ?PDO $pdo = null;
    /**
     * Stocke la connexion PDO une fois créée.
     * static => partagée dans toute l'application.
     */
    public static function getConnection(): PDO
    {
        //  Si on a déjà une connexion, on la renvoie directement
        if (self::$pdo instanceof PDO) {
            return self::$pdo;

        }

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $name = $_ENV['DB_NAME'] ?? 'boxing_social';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        // DSN = informations de connexion pour PDO (driver mysql + host + db + charset)
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // exceptions SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // fetch associatif par défaut
                PDO::ATTR_EMULATE_PREPARES => false,                // vraies requêtes préparées
            ]);

            return self::$pdo;
        } catch (PDOException $e) {
            throw new PDOException(
                'Connexion DB échouée: ' . $e->getMessage(),
                (int) $e->getCode()
            );
        }
    }
}
