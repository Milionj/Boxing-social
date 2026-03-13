<?php require dirname(__DIR__) . '/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('home_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/home.css">
  <script src="/js/search-autocomplete.js" defer></script>
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/partials/app-navbar.php'; ?>

  <main class="content app-main">
    <h1><?= htmlspecialchars($t->text('home_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars($t->text('home_intro'), ENT_QUOTES, 'UTF-8') ?></p>

    <form class="home-search" method="get" action="/search" autocomplete="off">
      <div class="autocomplete">
        <input
          type="text"
          name="q"
          placeholder="<?= htmlspecialchars($t->text('home_search_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
          data-user-autocomplete
          data-autocomplete-endpoint="/search/usernames"
          required
        >
        <div class="autocomplete-list" hidden></div>
      </div>
      <button class="btn" type="submit"><?= htmlspecialchars($t->text('home_search_button'), ENT_QUOTES, 'UTF-8') ?></button>
    </form>
  </main>
  <?php require dirname(__DIR__) . '/partials/app-footer.php'; ?>
</body>
</html>
