<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('posts_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260314b">
  <link rel="stylesheet" href="/css/posts-index.css?v=20260314e">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="posts-page app-main">
    <section class="posts-hero">
      <h1><?= htmlspecialchars($t->text('posts_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p>Retrouve l ensemble des publications et declarations de seances dans un fil continu, avec commentaires et interactions.</p>
    </section>

    <section class="posts-feed">
      <div class="posts-feed__head">
        <p class="posts-feed__eyebrow">Fil complet</p>
      </div>

      <div class="posts-feed__body">
        <?php $feedBasePath = '/posts'; ?>
        <?php require dirname(__DIR__, 2) . '/templates/posts/feed-list.php'; ?>
      </div>
    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
