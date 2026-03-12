<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Accueil</title>
  <link rel="stylesheet" href="/css/home.css">
  <script src="/js/search-autocomplete.js" defer></script>
</head>
<body>
  <nav class="nav">
    <div class="brand">BOXING SOCIAL</div>
    <div class="links">
      <a href="/profile">Profil</a>
      <a href="/posts/create">Publier</a>
      <a href="/friends">Amis</a>
      <a href="/messages">Messages</a>
      <a href="/notifications">Notifications</a>
      <a href="/search">Recherche</a>
      <?php if (($role ?? null) === 'admin'): ?>
        <a href="/admin">Administration</a>
      <?php endif; ?>
    </div>
    <div class="right">
      <span><?= htmlspecialchars((string) $user, ENT_QUOTES, 'UTF-8') ?></span>
      <form method="post" action="/logout" class="inline-form">
        <button class="btn" type="submit">Se deconnecter</button>
      </form>
    </div>
  </nav>

  <main class="content">
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
</body>
</html>
