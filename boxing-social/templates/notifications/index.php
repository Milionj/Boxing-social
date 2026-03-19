<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<?php
$notificationPresenter = new \App\Services\NotificationService();
$items = $notificationPresenter->presentMany($items, $t);
?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($t->text('notifications_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260317p">
  <link rel="stylesheet" href="/css/notifications-index.css?v=20260315o">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>

  <main class="notifications-page app-main">
    <section class="notifications-scene">
      <div class="notifications-backdrop">
        <a class="notifications-backdrop__card notifications-backdrop__card--active" href="/">
          <span class="notifications-backdrop__eyebrow"><?= htmlspecialchars($t->text('notifications_page_quick_nav'), ENT_QUOTES, 'UTF-8') ?></span>
          <strong><?= htmlspecialchars($t->text('home_title'), ENT_QUOTES, 'UTF-8') ?></strong>
          <p><?= htmlspecialchars($t->text('notifications_page_home_text'), ENT_QUOTES, 'UTF-8') ?></p>
        </a>

        <a class="notifications-backdrop__card" href="/friends">
          <span class="notifications-backdrop__eyebrow"><?= htmlspecialchars($t->text('notifications_page_shortcut'), ENT_QUOTES, 'UTF-8') ?></span>
          <strong><?= htmlspecialchars($t->text('nav_friends'), ENT_QUOTES, 'UTF-8') ?></strong>
          <p><?= htmlspecialchars($t->text('notifications_page_friends_text'), ENT_QUOTES, 'UTF-8') ?></p>
        </a>

        <a class="notifications-backdrop__card" href="/posts">
          <span class="notifications-backdrop__eyebrow"><?= htmlspecialchars($t->text('notifications_page_shortcut'), ENT_QUOTES, 'UTF-8') ?></span>
          <strong><?= htmlspecialchars($t->text('posts_heading'), ENT_QUOTES, 'UTF-8') ?></strong>
          <p><?= htmlspecialchars($t->text('notifications_page_posts_text'), ENT_QUOTES, 'UTF-8') ?></p>
        </a>

        <a class="notifications-backdrop__card" href="/search">
          <span class="notifications-backdrop__eyebrow"><?= htmlspecialchars($t->text('notifications_page_shortcut'), ENT_QUOTES, 'UTF-8') ?></span>
          <strong><?= htmlspecialchars($t->text('nav_search'), ENT_QUOTES, 'UTF-8') ?></strong>
          <p><?= htmlspecialchars($t->text('notifications_page_search_text'), ENT_QUOTES, 'UTF-8') ?></p>
        </a>

        <div class="notifications-backdrop__stats">
          <article class="notifications-backdrop__stat">
            <span><?= htmlspecialchars($t->text('notifications_page_stats_unread'), ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= (int) $unreadCount ?></strong>
          </article>
          <article class="notifications-backdrop__stat">
            <span><?= htmlspecialchars($t->text('notifications_page_stats_total'), ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= count($items) ?></strong>
          </article>
        </div>
      </div>

      <section class="notifications-sheet" aria-label="<?= htmlspecialchars($t->text('notifications_drawer_label'), ENT_QUOTES, 'UTF-8') ?>" data-notifications-scope>
        <header class="notifications-sheet__header">
          <div>
            <p class="notifications-sheet__eyebrow"><?= htmlspecialchars($t->text('notifications_drawer_label'), ENT_QUOTES, 'UTF-8') ?></p>
            <h1><?= htmlspecialchars($t->text('nav_notifications'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="notifications-sheet__intro"><?= htmlspecialchars($t->text('notifications_page_intro'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>

          <div class="notifications-sheet__meta">
            <span class="notifications-sheet__badge" data-notifications-badge data-count-format="suffix"><?= (int) $unreadCount ?> <?= htmlspecialchars($t->text('notifications_unread_count_suffix'), ENT_QUOTES, 'UTF-8') ?></span>
            <form method="post" action="/notifications/read-all" data-notifications-mark-all-form>
              <button class="notifications-sheet__button" type="submit"><?= htmlspecialchars($t->text('notifications_mark_all'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
          </div>
        </header>

        <p class="interaction-feedback is-error" data-interaction-feedback hidden></p>

        <?php if (empty($items)): ?>
          <section class="notifications-empty" data-notifications-empty>
            <h2><?= htmlspecialchars($t->text('notifications_page_empty_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($t->text('notifications_page_empty_text'), ENT_QUOTES, 'UTF-8') ?></p>
          </section>
        <?php else: ?>
          <section class="notifications-list" data-notifications-list>
            <?php foreach ($items as $n): ?>
              <article
                class="notification-card <?= ((int) $n['is_read'] === 0) ? 'is-unread' : '' ?>"
                data-notification-item
                data-notification-id="<?= (int) $n['id'] ?>"
                data-notification-open-url="<?= htmlspecialchars((string) ($n['open_url'] ?? $n['target_url'] ?? '/notifications'), ENT_QUOTES, 'UTF-8') ?>"
              >
                <div class="notification-card__head">
                  <div class="notification-card__type-wrap">
                    <span class="notification-card__dot"></span>
                    <strong class="notification-card__type">
                      <?= htmlspecialchars((string) ($n['display_type'] ?? $n['type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                  </div>

                  <time class="notification-card__time">
                    <?= htmlspecialchars((string) $n['created_at'], ENT_QUOTES, 'UTF-8') ?>
                  </time>
                </div>

                <p class="notification-card__content">
                  <?= htmlspecialchars((string) ($n['display_content'] ?? $n['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </p>

                <div class="notification-card__foot">
                  <p class="notification-card__actor">
                    <?php if (!empty($n['actor_username'])): ?>
                      <span><?= htmlspecialchars($t->text('notifications_actor'), ENT_QUOTES, 'UTF-8') ?></span>
                      <a href="/user?username=<?= rawurlencode((string) $n['actor_username']) ?>">
                        <?= htmlspecialchars((string) $n['actor_username'], ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    <?php else: ?>
                      <span><?= htmlspecialchars($t->text('notifications_actor'), ENT_QUOTES, 'UTF-8') ?></span> <?= htmlspecialchars($t->text('notifications_system'), ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                  </p>

                  <div class="notification-card__actions">
                    <a class="notification-card__open" href="<?= htmlspecialchars((string) ($n['open_url'] ?? $n['target_url'] ?? '/notifications'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('notifications_open'), ENT_QUOTES, 'UTF-8') ?></a>

                    <?php if ((int) $n['is_read'] === 0): ?>
                      <form method="post" action="/notifications/read" data-notification-read-form>
                        <input type="hidden" name="notification_id" value="<?= (int) $n['id'] ?>">
                        <button class="notification-card__read" type="submit"><?= htmlspecialchars($t->text('notifications_mark_read'), ENT_QUOTES, 'UTF-8') ?></button>
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
