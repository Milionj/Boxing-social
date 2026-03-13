<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\UserSettings;

final class SettingsController
{
    private UserSettings $settings;

    public function __construct()
    {
        $this->settings = new UserSettings();
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

    public function show(Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        $tableReady = $this->settings->tableExists();
        $settings = $tableReady ? $this->settings->findByUserId($userId) : $this->settings->defaults();
        $success = $_SESSION['success_settings'] ?? '';
        $errors = $_SESSION['errors_settings'] ?? [];

        unset($_SESSION['success_settings'], $_SESSION['errors_settings']);

        require dirname(__DIR__, 2) . '/templates/settings.php';
    }

    public function update(Request $request, Response $response): void
    {
        $userId = $this->requireAuth($response);
        if ($userId === null) {
            return;
        }

        if (!$this->settings->tableExists()) {
            $_SESSION['errors_settings'] = ['La table user_settings est absente. Execute la migration SQL avant de sauvegarder les preferences.'];
            $response->redirect('/settings');
            return;
        }

        $theme = (string) $request->input('theme', 'systeme');
        $language = (string) $request->input('language', 'francais');
        $parentalControls = (string) $request->input('parental_controls', '0');
        $notificationsEnabled = (string) $request->input('notifications_enabled', '1');

        $errors = [];

        if (!in_array($theme, ['systeme', 'clair', 'sombre'], true)) {
            $errors[] = 'Theme invalide.';
        }

        if (!in_array($language, ['francais', 'anglais'], true)) {
            $errors[] = 'Langue invalide.';
        }

        if (!in_array($parentalControls, ['0', '1'], true)) {
            $errors[] = 'Valeur invalide pour les controles parentaux.';
        }

        if (!in_array($notificationsEnabled, ['0', '1'], true)) {
            $errors[] = 'Valeur invalide pour les notifications.';
        }

        if ($errors !== []) {
            $_SESSION['errors_settings'] = $errors;
            $response->redirect('/settings');
            return;
        }

        $this->settings->upsert($userId, [
            'theme' => $theme,
            'language' => $language,
            'parental_controls' => (int) $parentalControls,
            'notifications_enabled' => (int) $notificationsEnabled,
        ]);

        $_SESSION['success_settings'] = 'Parametres mis a jour.';
        $response->redirect('/settings');
    }
}
