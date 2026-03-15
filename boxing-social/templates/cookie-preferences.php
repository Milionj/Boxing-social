<?php
declare(strict_types=1);

use App\Controllers\CookiePreferencesController;

require dirname(__DIR__) . '/templates/partials/app-locale.php';
$isLoggedIn = isset($_SESSION['user']['id']);
$preferencesCookieName = CookiePreferencesController::COOKIE_NAME;
?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($t->text('cookies_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/cookie-preferences.css?v=20260315i">
  <link rel="stylesheet" href="/css/scroll-top.css?v=20260315i">
</head>
<body class="cookies-page">
  <main class="cookies-shell">
    <header class="cookies-topbar">
      <a class="cookies-brand" href="/">
        <img src="/img/Bonlogo.png" alt="Logo Boxing Social">
        <span>
          <strong>Boxing Social</strong>
          <small>Communauté boxe</small>
        </span>
      </a>

      <nav class="cookies-nav" aria-label="Navigation publique">
        <a href="/"><?= $isLoggedIn ? 'Retour à l’accueil' : 'Accueil' ?></a>
        <a href="/privacy"><?= htmlspecialchars($t->text('nav_privacy'), ENT_QUOTES, 'UTF-8') ?></a>
        <a href="/contact"><?= htmlspecialchars($t->text('nav_contact'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php if (!$isLoggedIn): ?>
          <a href="/login">Connexion</a>
          <a class="cookies-nav__cta" href="/register">Inscription</a>
        <?php endif; ?>
      </nav>
    </header>

    <section class="cookies-hero">
      <p class="cookies-kicker"><?= htmlspecialchars($t->text('cookies_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
      <h1><?= htmlspecialchars($t->text('cookies_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('cookies_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <section class="cookies-card">
      <?php if ($saved): ?>
        <p class="msg-success"><?= htmlspecialchars($t->text('cookies_saved'), ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <div class="cookies-summary">
        <p><strong><?= htmlspecialchars($t->text('cookies_session_cookie_label'), ENT_QUOTES, 'UTF-8') ?> :</strong> <code>boxing_social_session</code></p>
        <p><strong><?= htmlspecialchars($t->text('cookies_preferences_cookie_label'), ENT_QUOTES, 'UTF-8') ?> :</strong> <code><?= htmlspecialchars($preferencesCookieName, ENT_QUOTES, 'UTF-8') ?></code></p>
      </div>

      <form class="cookies-form" method="post" action="/cookie-preferences">
        <article class="cookies-row cookies-row--locked">
          <div>
            <h2><?= htmlspecialchars($t->text('cookies_essential_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($t->text('cookies_essential_text'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <label class="cookies-toggle">
            <input type="checkbox" checked disabled>
            <span><?= htmlspecialchars($t->text('cookies_required'), ENT_QUOTES, 'UTF-8') ?></span>
          </label>
        </article>

        <article class="cookies-row">
          <div>
            <h2><?= htmlspecialchars($t->text('cookies_analytics_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($t->text('cookies_analytics_text'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <label class="cookies-toggle">
            <input type="checkbox" name="analytics" value="1" <?= !empty($preferences['analytics']) ? 'checked' : '' ?>>
            <span><?= htmlspecialchars($t->text('cookies_optional'), ENT_QUOTES, 'UTF-8') ?></span>
          </label>
        </article>

        <article class="cookies-row">
          <div>
            <h2><?= htmlspecialchars($t->text('cookies_personalization_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($t->text('cookies_personalization_text'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <label class="cookies-toggle">
            <input type="checkbox" name="personalization" value="1" <?= !empty($preferences['personalization']) ? 'checked' : '' ?>>
            <span><?= htmlspecialchars($t->text('cookies_optional'), ENT_QUOTES, 'UTF-8') ?></span>
          </label>
        </article>

        <article class="cookies-row">
          <div>
            <h2><?= htmlspecialchars($t->text('cookies_marketing_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($t->text('cookies_marketing_text'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <label class="cookies-toggle">
            <input type="checkbox" name="marketing" value="1" <?= !empty($preferences['marketing']) ? 'checked' : '' ?>>
            <span><?= htmlspecialchars($t->text('cookies_optional'), ENT_QUOTES, 'UTF-8') ?></span>
          </label>
        </article>

        <div class="cookies-actions">
          <button type="submit"><?= htmlspecialchars($t->text('cookies_save'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
      </form>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/cookie-notice.php'; ?>
  <?php require dirname(__DIR__) . '/templates/partials/scroll-top.php'; ?>
</body>
</html>
