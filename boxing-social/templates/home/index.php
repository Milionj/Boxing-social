<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Accueil</title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/home.css">
  <script src="/js/search-autocomplete.js" defer></script>
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/partials/app-navbar.php'; ?>

  <main class="content app-main">
    <h1>Accueil</h1>
    <p>Retrouve rapidement une personne, une publication ou un sujet de discussion.</p>

    <form class="home-search" method="get" action="/search" autocomplete="off">
      <div class="autocomplete">
        <input
          type="text"
          name="q"
          placeholder="Rechercher un pseudo ou une publication"
          data-user-autocomplete
          data-autocomplete-endpoint="/search/usernames"
          required
        >
        <div class="autocomplete-list" hidden></div>
      </div>
      <button class="btn" type="submit">Rechercher</button>
    </form>
  </main>
  <?php require dirname(__DIR__) . '/partials/app-footer.php'; ?>
</body>
</html>
