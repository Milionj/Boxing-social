<?php
declare(strict_types=1);

use App\Controllers\CookiePreferencesController;

require dirname(__DIR__) . '/templates/partials/app-locale.php';
?>
<?php $isLoggedIn = isset($_SESSION['user']['id']); ?>
<?php $preferencesCookieName = CookiePreferencesController::COOKIE_NAME; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($t->text('privacy_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/privacy-public.css?v=20260315i">
  <link rel="stylesheet" href="/css/cookie-preferences.css?v=20260315i">
  <link rel="stylesheet" href="/css/scroll-top.css?v=20260317a">
</head>
<body class="privacy-page">
  <main class="privacy-shell">
    <header class="privacy-topbar">
      <a class="privacy-brand" href="/">
        <img src="/img/Bonlogo.png" alt="Logo Boxing Social">
        <span>
          <strong>Boxing Social</strong>
          <small>Communauté boxe</small>
        </span>
      </a>

      <nav class="privacy-nav" aria-label="Navigation publique">
        <a href="/"><?= $isLoggedIn ? 'Retour à l’accueil' : 'Accueil' ?></a>
        <a href="/contact"><?= htmlspecialchars($t->text('nav_contact'), ENT_QUOTES, 'UTF-8') ?></a>
        <a href="/cookie-preferences"><?= htmlspecialchars($t->text('cookies_title'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php if (!$isLoggedIn): ?>
          <a href="/login">Connexion</a>
          <a class="privacy-nav__cta" href="/register">Inscription</a>
        <?php endif; ?>
      </nav>
    </header>

    <section class="privacy-hero">
      <p class="privacy-kicker"><?= htmlspecialchars($t->text('privacy_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
      <h1><?= htmlspecialchars($t->text('privacy_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('privacy_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <section class="privacy-card">
      <div class="privacy-card__head">
        <div>
          <p class="privacy-card__eyebrow"><?= htmlspecialchars($t->text('privacy_card_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($t->text('privacy_card_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
          <p><?= htmlspecialchars($t->text('privacy_card_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <p class="privacy-meta"><?= htmlspecialchars($t->text('privacy_updated_at'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <article class="privacy-article">
        <h2><?= htmlspecialchars($t->text('privacy_article_1_title'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t->text('privacy_article_1_text'), ENT_QUOTES, 'UTF-8') ?></p>
      </article>

      <article class="privacy-article">
        <h2><?= htmlspecialchars($t->text('privacy_article_2_title'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t->text('privacy_article_2_text'), ENT_QUOTES, 'UTF-8') ?></p>
      </article>

      <article class="privacy-article">
        <h2><?= htmlspecialchars($t->text('privacy_article_3_title'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t->text('privacy_article_3_text'), ENT_QUOTES, 'UTF-8') ?></p>
      </article>

      <article class="privacy-article">
        <h2><?= htmlspecialchars($t->text('privacy_article_4_title'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t->text('privacy_article_4_text'), ENT_QUOTES, 'UTF-8') ?></p>
      </article>

      <article class="privacy-article">
        <h2><?= htmlspecialchars($t->text('privacy_article_5_title'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t->text('privacy_article_5_text'), ENT_QUOTES, 'UTF-8') ?></p>
      </article>

      <article class="privacy-article">
        <h2><?= htmlspecialchars($t->text('privacy_article_6_title'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t->text('privacy_article_6_text'), ENT_QUOTES, 'UTF-8') ?></p>
        <div class="privacy-cookie-box">
          <p><strong><?= htmlspecialchars($t->text('cookies_session_cookie_label'), ENT_QUOTES, 'UTF-8') ?> :</strong> <code>boxing_social_session</code></p>
          <p><strong><?= htmlspecialchars($t->text('privacy_cookie_purpose_label'), ENT_QUOTES, 'UTF-8') ?> :</strong> <?= htmlspecialchars($t->text('privacy_cookie_purpose_text'), ENT_QUOTES, 'UTF-8') ?></p>
          <p><strong><?= htmlspecialchars($t->text('privacy_cookie_duration_label'), ENT_QUOTES, 'UTF-8') ?> :</strong> <?= htmlspecialchars($t->text('privacy_cookie_duration_text'), ENT_QUOTES, 'UTF-8') ?></p>
          <p><strong><?= htmlspecialchars($t->text('cookies_preferences_cookie_label'), ENT_QUOTES, 'UTF-8') ?> :</strong> <code><?= htmlspecialchars($preferencesCookieName, ENT_QUOTES, 'UTF-8') ?></code></p>
        </div>
      </article>

      <article class="privacy-article">
        <h2><?= htmlspecialchars($t->text('privacy_article_7_title'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t->text('privacy_article_7_text'), ENT_QUOTES, 'UTF-8') ?></p>
        <p class="privacy-cookie-link">
          <a href="/cookie-preferences"><?= htmlspecialchars($t->text('privacy_cookie_preferences_link'), ENT_QUOTES, 'UTF-8') ?></a>
        </p>
      </article>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/cookie-notice.php'; ?>
  <?php require dirname(__DIR__) . '/templates/partials/scroll-top.php'; ?>
</body>
</html>
