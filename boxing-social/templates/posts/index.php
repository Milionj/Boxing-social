<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('posts_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260315n">
  <link rel="stylesheet" href="/css/posts-index.css?v=20260315n">
</head>
<body
  class="app-shell"
  data-post-interaction-error="<?= htmlspecialchars($t->text('posts_interaction_error'), ENT_QUOTES, 'UTF-8') ?>"
  data-comment-delete-label="<?= htmlspecialchars($t->text('posts_delete_comment'), ENT_QUOTES, 'UTF-8') ?>"
>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="posts-page app-main">
    <section class="posts-hero">
      <h1><?= htmlspecialchars($t->text('posts_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('posts_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <div class="posts-layout">
      <section class="posts-feed">
        <div class="posts-feed__head">
          <p class="posts-feed__eyebrow"><?= htmlspecialchars($t->text('posts_feed_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="posts-feed__body">
          <?php $feedBasePath = '/posts'; ?>
          <?php require dirname(__DIR__, 2) . '/templates/posts/feed-list.php'; ?>
        </div>
      </section>

      <aside class="feed-side-rail" aria-label="Raccourci amis">
        <section class="feed-side-card">
          <div class="feed-side-card__head">
            <div>
              <p class="feed-side-card__eyebrow">Amis</p>
              <h2><?= htmlspecialchars($t->text('friends_quick_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <a href="/friends"><?= htmlspecialchars($t->text('friends_quick_view_all'), ENT_QUOTES, 'UTF-8') ?></a>
          </div>

          <?php if (empty($quickFriends)): ?>
            <p class="feed-side-card__empty"><?= htmlspecialchars($t->text('friends_quick_empty'), ENT_QUOTES, 'UTF-8') ?></p>
          <?php else: ?>
            <div class="feed-side-card__list">
              <?php foreach ($quickFriends as $friend): ?>
                <a class="feed-side-card__friend" href="/user?username=<?= rawurlencode((string) $friend['username']) ?>">
                  <span class="feed-side-card__avatar"><?= htmlspecialchars(strtoupper(substr((string) $friend['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="feed-side-card__name"><?= htmlspecialchars((string) $friend['username'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </aside>
    </div>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
  <script src="/js/post-interactions.js?v=20260315n" defer></script>
</body>
</html>
