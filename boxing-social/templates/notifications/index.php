<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Notifications</title>
  <link rel="stylesheet" href="/css/notifications-index.css">
</head>
<body>
  <main class="page">
    <h1>Notifications</h1>
    <p><strong>Non lues:</strong> <?= (int) $unreadCount ?></p>

    <p>
      <a href="/">Accueil</a> |
      <a href="/friends">Amis</a> |
      <a href="/posts">Posts</a>
    </p>

    <form method="post" action="/notifications/read-all">
      <button type="submit">Tout marquer comme lu</button>
    </form>

    <hr>

    <?php if (empty($items)): ?>
      <p>Aucune notification.</p>
    <?php else: ?>
      <?php foreach ($items as $n): ?>
        <div class="item <?= ((int) $n['is_read'] === 0) ? 'unread' : '' ?>">
          <p>
            <strong><?= htmlspecialchars((string) $n['type'], ENT_QUOTES, 'UTF-8') ?></strong>
            -
            <?= htmlspecialchars((string) ($n['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
          </p>
          <p>
            <small>
              acteur: <?= htmlspecialchars((string) ($n['actor_username'] ?? 'system'), ENT_QUOTES, 'UTF-8') ?>
              | <?= htmlspecialchars((string) $n['created_at'], ENT_QUOTES, 'UTF-8') ?>
            </small>
          </p>

          <?php if ((int) $n['is_read'] === 0): ?>
            <form method="post" action="/notifications/read">
              <input type="hidden" name="notification_id" value="<?= (int) $n['id'] ?>">
              <button type="submit">Marquer comme lu</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</body>
</html>
