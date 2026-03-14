<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Notifications</title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260314b">
  <link rel="stylesheet" href="/css/notifications-index.css?v=20260314c">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>

  <main class="notifications-page app-main">
    <section class="notifications-scene">
      <div class="notifications-backdrop">
        <a class="notifications-backdrop__card notifications-backdrop__card--active" href="/">
          <span class="notifications-backdrop__eyebrow">Navigation rapide</span>
          <strong>Accueil</strong>
          <p>Reviens sur le fil, les seances et les publications recentes.</p>
        </a>

        <a class="notifications-backdrop__card" href="/friends">
          <span class="notifications-backdrop__eyebrow">Raccourci</span>
          <strong>Amis</strong>
          <p>Consulte tes demandes recues, les invitations envoyees et ton reseau.</p>
        </a>

        <a class="notifications-backdrop__card" href="/posts">
          <span class="notifications-backdrop__eyebrow">Raccourci</span>
          <strong>Publications</strong>
          <p>Ouvre le fil complet pour commenter, aimer et parcourir les posts.</p>
        </a>

        <a class="notifications-backdrop__card" href="/search">
          <span class="notifications-backdrop__eyebrow">Raccourci</span>
          <strong>Recherche</strong>
          <p>Retrouve rapidement un pseudo, une publication ou un sujet precis.</p>
        </a>

        <div class="notifications-backdrop__stats">
          <article class="notifications-backdrop__stat">
            <span>Non lues</span>
            <strong><?= (int) $unreadCount ?></strong>
          </article>
          <article class="notifications-backdrop__stat">
            <span>Total affiche</span>
            <strong><?= count($items) ?></strong>
          </article>
        </div>
      </div>

      <section class="notifications-sheet" aria-label="Centre de notifications">
        <header class="notifications-sheet__header">
          <div>
            <p class="notifications-sheet__eyebrow">Centre de notifications</p>
            <h1>Notifications</h1>
            <p class="notifications-sheet__intro">Retrouve ici les interactions recentes et ouvre directement la bonne page.</p>
          </div>

          <div class="notifications-sheet__meta">
            <span class="notifications-sheet__badge"><?= (int) $unreadCount ?> non lues</span>
            <form method="post" action="/notifications/read-all">
              <button class="notifications-sheet__button" type="submit">Tout marquer comme lu</button>
            </form>
          </div>
        </header>

        <?php if (empty($items)): ?>
          <section class="notifications-empty">
            <h2>Aucune notification</h2>
            <p>Ton centre de notifications se remplira des qu une interaction arrivera sur ton compte.</p>
          </section>
        <?php else: ?>
          <section class="notifications-list">
            <?php foreach ($items as $n): ?>
              <article class="notification-card <?= ((int) $n['is_read'] === 0) ? 'is-unread' : '' ?>">
                <div class="notification-card__head">
                  <div class="notification-card__type-wrap">
                    <span class="notification-card__dot"></span>
                    <strong class="notification-card__type">
                      <?= htmlspecialchars((string) $n['type'], ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                  </div>

                  <time class="notification-card__time">
                    <?= htmlspecialchars((string) $n['created_at'], ENT_QUOTES, 'UTF-8') ?>
                  </time>
                </div>

                <p class="notification-card__content">
                  <?= htmlspecialchars((string) ($n['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </p>

                <div class="notification-card__foot">
                  <p class="notification-card__actor">
                    <?php if (!empty($n['actor_username'])): ?>
                      <span>Acteur :</span>
                      <a href="/user?username=<?= rawurlencode((string) $n['actor_username']) ?>">
                        <?= htmlspecialchars((string) $n['actor_username'], ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    <?php else: ?>
                      <span>Acteur :</span> systeme
                    <?php endif; ?>
                  </p>

                  <div class="notification-card__actions">
                    <a class="notification-card__open" href="<?= htmlspecialchars((string) $n['target_url'], ENT_QUOTES, 'UTF-8') ?>">Ouvrir</a>

                    <?php if ((int) $n['is_read'] === 0): ?>
                      <form method="post" action="/notifications/read">
                        <input type="hidden" name="notification_id" value="<?= (int) $n['id'] ?>">
                        <button class="notification-card__read" type="submit">Marquer comme lu</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </section>
        <?php endif; ?>
      </section>
    </section>
  </main>

  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
