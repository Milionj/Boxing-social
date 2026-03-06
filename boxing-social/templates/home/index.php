<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Accueil</title>
  <link rel="stylesheet" href="/css/home.css">
</head>
<body>
  <nav class="nav">
    <div class="brand">BOXING SOCIAL</div>
    <div class="links">
      <a href="/profile">Profil</a>
      <a href="/posts/create">Publier</a>
      <a href="/friends">Amis</a>
      <a href="/messages">Message</a>
      <a href="/notifications">Notifications</a>
      <?php if (($role ?? null) === 'admin'): ?>
        <a href="/admin">Admin</a>
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
    <p>...</p>
  </main>
</body>
</html>
