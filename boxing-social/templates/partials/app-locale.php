<?php
declare(strict_types=1);

$appLanguage = 'francais';

// Cette partial prepare la langue tres tot dans le rendu.
// On la charge avant le <html> des pages qui doivent reactiver
// la preference "language" dans le titre, les labels et les menus.
if (is_int($_SESSION['user']['id'] ?? null)) {
    $appLocaleUserId = (int) $_SESSION['user']['id'];
    $appLocaleSettings = new \App\Models\UserSettings();
    $appLanguage = $appLocaleSettings->languageForUser($appLocaleUserId);
}

$t = new \App\Services\Translator($appLanguage);
$htmlLang = $t->htmlLang();
