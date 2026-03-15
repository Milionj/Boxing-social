<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

final class CookiePreferencesController
{
    public const COOKIE_NAME = 'boxing_social_cookie_preferences';

    /**
     * On stocke ici uniquement les preferences de consentement futures.
     * Tant que le site n'utilise pas de cookies non essentiels, ces valeurs
     * servent surtout de centre de preferences prete pour la suite.
     */
    public function show(Request $request, Response $response): void
    {
        $saved = $request->input('saved') === '1';
        $preferences = $this->readPreferencesCookie();

        ob_start();
        require dirname(__DIR__, 2) . '/templates/cookie-preferences.php';
        $response->html((string) ob_get_clean());
    }

    public function update(Request $request, Response $response): void
    {
        $preferences = [
            'essential' => true,
            'analytics' => $request->input('analytics') === '1',
            'personalization' => $request->input('personalization') === '1',
            'marketing' => $request->input('marketing') === '1',
            'updated_at' => date('c'),
        ];

        $this->writePreferencesCookie($preferences);
        $response->redirect('/cookie-preferences?saved=1');
    }

    public function readPreferencesCookie(): array
    {
        $raw = (string) ($_COOKIE[self::COOKIE_NAME] ?? '');

        if ($raw === '') {
            return $this->defaultPreferences();
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $this->defaultPreferences();
        }

        return [
            'essential' => true,
            'analytics' => (bool) ($decoded['analytics'] ?? false),
            'personalization' => (bool) ($decoded['personalization'] ?? false),
            'marketing' => (bool) ($decoded['marketing'] ?? false),
            'updated_at' => (string) ($decoded['updated_at'] ?? ''),
        ];
    }

    private function writePreferencesCookie(array $preferences): void
    {
        $appUrl = (string) ($_ENV['APP_URL'] ?? '');
        $appUrlScheme = parse_url($appUrl, PHP_URL_SCHEME);
        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
            || $appUrlScheme === 'https'
        );

        setcookie(self::COOKIE_NAME, json_encode($preferences, JSON_UNESCAPED_SLASHES), [
            // 6 mois pour memoriser un choix de preference utilisateur.
            'expires' => time() + (180 * 24 * 60 * 60),
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function defaultPreferences(): array
    {
        return [
            'essential' => true,
            'analytics' => false,
            'personalization' => false,
            'marketing' => false,
            'updated_at' => '',
        ];
    }
}
