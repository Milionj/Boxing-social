<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('posts_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260317p">
  <link rel="stylesheet" href="/css/posts-index.css?v=20260317e">
</head>
<body
  class="app-shell"
  data-post-interaction-error="<?= htmlspecialchars($t->text('posts_interaction_error'), ENT_QUOTES, 'UTF-8') ?>"
  data-comment-delete-label="<?= htmlspecialchars($t->text('posts_delete_comment'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-schedule-endpoint="/sports/mma/schedule"
  data-sports-event-endpoint="/sports/mma/event"
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
        <section class="feed-side-card feed-side-card--collapsible is-collapsed" data-side-card="quick-friends">
          <div class="feed-side-card__head">
            <div>
              <p class="feed-side-card__eyebrow">Amis</p>
              <h2><?= htmlspecialchars($t->text('friends_quick_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <div class="feed-side-card__head-actions">
              <a href="/friends"><?= htmlspecialchars($t->text('friends_quick_view_all'), ENT_QUOTES, 'UTF-8') ?></a>
              <button type="button" class="feed-side-card__toggle" data-side-card-toggle aria-expanded="false" aria-controls="quick-friends-posts">
                <span data-side-card-toggle-label><?= htmlspecialchars($t->text('feed_side_expand'), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="feed-side-card__toggle-icon" aria-hidden="true">⌃</span>
              </button>
            </div>
          </div>

          <div class="feed-side-card__body" id="quick-friends-posts" data-side-card-body hidden>
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
          </div>
        </section>

        <section class="feed-side-card feed-side-card--sports feed-side-card--collapsible is-collapsed" data-side-card="mma-insights">
          <div class="feed-side-card__head">
            <div>
              <p class="feed-side-card__eyebrow">MMA</p>
              <h2><?= htmlspecialchars($t->text('sports_preview_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <div class="feed-side-card__head-actions">
              <button type="button" class="feed-side-card__toggle" data-side-card-toggle aria-expanded="false" aria-controls="mma-widget-posts">
                <span data-side-card-toggle-label><?= htmlspecialchars($t->text('feed_side_expand'), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="feed-side-card__toggle-icon" aria-hidden="true">⌃</span>
              </button>
            </div>
          </div>

          <div class="feed-side-card__body" id="mma-widget-posts" data-side-card-body hidden>
            <div class="feed-side-sports" data-inline-sports-widget data-sports-season="<?= (int) date('Y') ?>"></div>
          </div>
        </section>
      </aside>
    </div>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
  <script src="/js/post-interactions.js?v=20260317j" defer></script>
</body>
</html>
