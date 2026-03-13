<?php
declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use PDO;
use PDOException;

final class UserSettings
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function defaults(): array
    {
        // Valeurs de secours utilisées :
        // - si l'utilisateur n'a encore rien enregistré
        // - ou si la table existe mais qu'aucune ligne n'est créée pour lui
        return [
            'theme' => 'systeme',
            'language' => 'francais',
            'parental_controls' => 0,
            'notifications_enabled' => 1,
        ];
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT theme, language, parental_controls, notifications_enabled
             FROM user_settings
             WHERE user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $settings = $stmt->fetch();

        if (!$settings) {
            return $this->defaults();
        }

        return [
            'theme' => (string) ($settings['theme'] ?? 'systeme'),
            'language' => (string) ($settings['language'] ?? 'francais'),
            'parental_controls' => (int) ($settings['parental_controls'] ?? 0),
            'notifications_enabled' => (int) ($settings['notifications_enabled'] ?? 1),
        ];
    }

    public function upsert(int $userId, array $settings): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO user_settings (user_id, theme, language, parental_controls, notifications_enabled)
             VALUES (:user_id, :theme, :language, :parental_controls, :notifications_enabled)
             ON DUPLICATE KEY UPDATE
               theme = VALUES(theme),
               language = VALUES(language),
               parental_controls = VALUES(parental_controls),
               notifications_enabled = VALUES(notifications_enabled),
               updated_at = CURRENT_TIMESTAMP'
        );

        return $stmt->execute([
            'user_id' => $userId,
            'theme' => $settings['theme'],
            'language' => $settings['language'],
            'parental_controls' => $settings['parental_controls'],
            'notifications_enabled' => $settings['notifications_enabled'],
        ]);
    }

    public function notificationsEnabledForUser(int $userId): bool
    {
        // On garde un comportement "souple" :
        // si la table n'existe pas encore, l'application continue comme avant
        // et considère que les notifications sont autorisées.
        if (!$this->tableExists()) {
            return true;
        }

        $stmt = $this->pdo->prepare(
            'SELECT notifications_enabled
             FROM user_settings
             WHERE user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            return true;
        }

        return (int) $value === 1;
    }

    public function themeForUser(int $userId): string
    {
        if (!$this->tableExists()) {
            return 'systeme';
        }

        $stmt = $this->pdo->prepare(
            'SELECT theme
             FROM user_settings
             WHERE user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            return 'systeme';
        }

        $theme = (string) $value;
        return in_array($theme, ['systeme', 'clair', 'sombre'], true) ? $theme : 'systeme';
    }

    public function languageForUser(int $userId): string
    {
        // Meme idee que pour le theme :
        // on relit la preference si elle existe, sinon on reste sur le francais.
        if (!$this->tableExists()) {
            return 'francais';
        }

        $stmt = $this->pdo->prepare(
            'SELECT language
             FROM user_settings
             WHERE user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            return 'francais';
        }

        $language = (string) $value;
        return in_array($language, ['francais', 'anglais'], true) ? $language : 'francais';
    }

    public function tableExists(): bool
    {
        try {
            $this->pdo->query('SELECT 1 FROM user_settings LIMIT 1');
            return true;
        } catch (PDOException) {
            return false;
        }
    }
}
