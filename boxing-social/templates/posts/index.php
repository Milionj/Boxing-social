<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Feed Posts</title>
</head>
<body>
  <h1>Derniers posts</h1>

  <p>
    <a href="/">Accueil</a> |
    <a href="/posts/create">Creer un post</a> |
    <a href="/profile">Mon profil</a>
  </p>

  <?php if (empty($feed)): ?>
    <p>Aucun post pour le moment.</p>
  <?php else: ?>
    <?php foreach ($feed as $post): ?>
      <article style="border:1px solid #ddd;padding:12px;margin:12px 0;">
        <h3><?= htmlspecialchars((string)($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
        <p><strong>Auteur:</strong> <?= htmlspecialchars((string)$post['username'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><?= nl2br(htmlspecialchars((string)$post['content'], ENT_QUOTES, 'UTF-8')) ?></p>

        <?php if (!empty($post['image_path'])): ?>
          <p><img src="<?= htmlspecialchars((string)$post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Image post" style="max-width:280px;height:auto;"></p>
        <?php endif; ?>

        <?php if (!empty($post['location'])): ?>
          <p><strong>Lieu:</strong> <?= htmlspecialchars((string)$post['location'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <p><small><?= htmlspecialchars((string)$post['created_at'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string)$post['visibility'], ENT_QUOTES, 'UTF-8') ?></small></p>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
