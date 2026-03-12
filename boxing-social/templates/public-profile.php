<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Profil public</title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/public-profile.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <section class="hero">
      <div class="identity">
        <?php if (!empty($user['avatar_path'])): ?>
          <img class="avatar" src="<?= htmlspecialchars((string) $user['avatar_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Photo de profil">
        <?php else: ?>
          <div class="avatar avatar-fallback"><?= strtoupper(substr((string) $user['username'], 0, 1)) ?></div>
        <?php endif; ?>

        <div>
          <p class="eyebrow">Profil public</p>
          <h1><?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?></h1>
          <p class="meta">Membre depuis <?= htmlspecialchars((string) $user['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>

      <div class="actions">
        <form method="post" action="/friends/send">
          <input type="hidden" name="username" value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit">Ajouter en ami</button>
        </form>
        <form method="get" action="/messages">
          <input type="hidden" name="username" value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit">Envoyer un message</button>
        </form>
      </div>
    </section>

    <section class="panel">
      <h2>Bio</h2>
      <p><?= nl2br(htmlspecialchars((string) ($user['bio'] ?? 'Cette personne n a pas encore renseigne sa bio.'), ENT_QUOTES, 'UTF-8')) ?></p>
    </section>

    <section class="panel">
      <div class="section-head">
        <h2>Publications publiques</h2>
        <span><?= count($posts) ?> publication(s)</span>
      </div>

      <?php if (empty($posts)): ?>
        <p class="muted">Aucune publication publique pour le moment.</p>
      <?php else: ?>
        <div class="posts">
          <?php foreach ($posts as $post): ?>
            <article class="post-card">
              <h3>
                <a href="/post?id=<?= (int) $post['id'] ?>">
                  <?= htmlspecialchars((string) ($post['title'] ?: 'Publication sans titre'), ENT_QUOTES, 'UTF-8') ?>
                </a>
              </h3>
              <p><?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?></p>
              <?php if (!empty($post['image_path'])): ?>
                <img src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Image de publication">
              <?php endif; ?>
              <?php if (!empty($post['location'])): ?>
                <p class="meta">Lieu : <?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></p>
              <?php endif; ?>
              <p class="meta"><?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($totalPages > 1): ?>
        <nav class="pagination">
          <?php if ($currentPage > 1): ?>
            <a href="/user?username=<?= rawurlencode((string) $user['username']) ?>&page=<?= $currentPage - 1 ?>">Page precedente</a>
          <?php endif; ?>

          <span>Page <?= $currentPage ?> / <?= $totalPages ?></span>

          <?php if ($currentPage < $totalPages): ?>
            <a href="/user?username=<?= rawurlencode((string) $user['username']) ?>&page=<?= $currentPage + 1 ?>">Page suivante</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/partials/app-footer.php'; ?>
</body>
</html>
