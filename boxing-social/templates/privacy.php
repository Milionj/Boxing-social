<?php require dirname(__DIR__) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('privacy_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260314b">
  <link rel="stylesheet" href="/css/static-page.css?v=20260313m">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/templates/partials/app-navbar.php'; ?>
  <main class="static-page app-main">
    <section class="static-hero">
      <h1><?= htmlspecialchars($t->text('privacy_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('privacy_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <section class="static-card static-card--reading legal-copy">
      <div class="static-card__head">
        <div>
          <p class="static-card__eyebrow"><?= htmlspecialchars($t->text('privacy_card_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($t->text('privacy_card_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
          <p><?= htmlspecialchars($t->text('privacy_card_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>

      <p class="static-meta"><?= htmlspecialchars($t->text('privacy_updated_at'), ENT_QUOTES, 'UTF-8') ?></p>

      <h2><?= htmlspecialchars($t->text('privacy_article_1_title'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p><?= htmlspecialchars($t->text('privacy_article_1_text'), ENT_QUOTES, 'UTF-8') ?></p>

      <h2><?= htmlspecialchars($t->text('privacy_article_2_title'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p><?= htmlspecialchars($t->text('privacy_article_2_text'), ENT_QUOTES, 'UTF-8') ?></p>

      <h2><?= htmlspecialchars($t->text('privacy_article_3_title'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p><?= htmlspecialchars($t->text('privacy_article_3_text'), ENT_QUOTES, 'UTF-8') ?></p>

      <h2><?= htmlspecialchars($t->text('privacy_article_4_title'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p><?= htmlspecialchars($t->text('privacy_article_4_text'), ENT_QUOTES, 'UTF-8') ?></p>

      <h2><?= htmlspecialchars($t->text('privacy_article_5_title'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p><?= htmlspecialchars($t->text('privacy_article_5_text'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
