<?php require dirname(__DIR__) . '/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('home_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260314b">
  <link rel="stylesheet" href="/css/home.css?v=20260314c">
  <link rel="stylesheet" href="/css/posts-index.css?v=20260314e">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/partials/app-navbar.php'; ?>

  <main class="content app-main">
    <section class="home-hero">
      <p class="home-panel__eyebrow">Accueil</p>
      <h1 class="home-kicker">Partagez / Proposez</h1>
      <p class="home-intro">Retrouve les publications, les seances d entrainement et les partenaires sans quitter ton fil principal.</p>
    </section>

    <section class="home-feed">
      <div class="home-feed__head">
        <p class="home-panel__eyebrow">Dernieres publications</p>
        <a class="home-panel__link" href="/posts">Voir le fil complet</a>
      </div>
      <div class="home-feed__body">
        <?php $feedBasePath = '/'; ?>
        <?php $feedContext = 'home'; ?>
        <?php require dirname(__DIR__) . '/posts/feed-list.php'; ?>
      </div>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/partials/app-footer.php'; ?>
</body>
</html>
