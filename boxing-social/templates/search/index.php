<?php require dirname(__DIR__) . '/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($t->text('nav_search'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260315o">
  <link rel="stylesheet" href="/css/search-index.css?v=20260315o">
  <script src="/js/search-autocomplete.js" defer></script>
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/partials/app-navbar.php'; ?>

  <main class="search-page app-main">
    <section class="search-hero">
      <h1><?= htmlspecialchars($t->text('search_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('search_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <section class="search-card">
      <form class="search-form" method="get" action="/search" autocomplete="off">
        <div class="autocomplete">
          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="<?= htmlspecialchars($t->text('search_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
            data-user-autocomplete
            data-autocomplete-endpoint="/search/usernames"
            required
          >
          <div class="autocomplete-list" hidden></div>
        </div>
        <button type="submit"><?= htmlspecialchars($t->text('home_search_button'), ENT_QUOTES, 'UTF-8') ?></button>
      </form>

      <div class="search-shortcuts">
        <a href="/"><?= htmlspecialchars($t->text('home_title'), ENT_QUOTES, 'UTF-8') ?></a>
        <a href="/posts"><?= htmlspecialchars($t->text('posts_title'), ENT_QUOTES, 'UTF-8') ?></a>
        <a href="/friends"><?= htmlspecialchars($t->text('nav_friends'), ENT_QUOTES, 'UTF-8') ?></a>
        <a href="/messages"><?= htmlspecialchars($t->text('nav_messages'), ENT_QUOTES, 'UTF-8') ?></a>
      </div>
    </section>

    <?php if ($query === ''): ?>
      <section class="search-empty card">
        <h2><?= htmlspecialchars($t->text('search_empty_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t->text('search_empty_text'), ENT_QUOTES, 'UTF-8') ?></p>
      </section>
    <?php else: ?>
      <section class="search-results">
        <section class="search-panel card">
          <div class="search-panel__head">
            <div>
              <h2><?= htmlspecialchars($t->text('search_users_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <span class="search-panel__count"><?= count($users) ?> <?= htmlspecialchars($t->text('search_results_suffix'), ENT_QUOTES, 'UTF-8') ?></span>
          </div>

          <?php if ($users === []): ?>
            <p class="search-muted"><?= htmlspecialchars($t->text('search_no_users'), ENT_QUOTES, 'UTF-8') ?></p>
          <?php else: ?>
            <div class="search-grid">
              <?php foreach ($users as $user): ?>
                <article class="search-result-card" data-social-scope>
                  <div class="search-result-card__head">
                    <div class="search-result-card__avatar"><?= htmlspecialchars(strtoupper(substr((string) $user['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                    <div>
                      <h3>
                        <a class="search-user-link" href="/user?username=<?= rawurlencode((string) $user['username']) ?>">
                          <?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                      </h3>
                    </div>
                  </div>

                  <p class="search-result-card__text">
                    <?= htmlspecialchars((string) ($user['bio'] ?? $t->text('search_no_bio')), ENT_QUOTES, 'UTF-8') ?>
                  </p>

                  <form method="post" action="/friends/send" data-friend-send-form>
                    <input type="hidden" name="username" value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
                    <button
                      type="submit"
                      data-friend-send-button
                      data-label-default="<?= htmlspecialchars($t->text('search_send_request'), ENT_QUOTES, 'UTF-8') ?>"
                      data-label-sent="<?= htmlspecialchars($t->text('friends_request_sent'), ENT_QUOTES, 'UTF-8') ?>"
                    ><?= htmlspecialchars($t->text('search_send_request'), ENT_QUOTES, 'UTF-8') ?></button>
                  </form>
                  <p class="interaction-feedback" data-interaction-feedback hidden></p>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>

        <section class="search-panel card">
          <div class="search-panel__head">
            <div>
              <h2><?= htmlspecialchars($t->text('search_posts_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <span class="search-panel__count"><?= count($posts) ?> <?= htmlspecialchars($t->text('search_results_suffix'), ENT_QUOTES, 'UTF-8') ?></span>
          </div>

          <?php if ($posts === []): ?>
            <p class="search-muted"><?= htmlspecialchars($t->text('search_no_posts'), ENT_QUOTES, 'UTF-8') ?></p>
          <?php else: ?>
            <div class="search-grid">
              <?php foreach ($posts as $post): ?>
                <article class="search-result-card search-result-card--post">
                  <p class="search-post-meta">
                    <?= htmlspecialchars($t->text('post_by'), ENT_QUOTES, 'UTF-8') ?>
                    <a class="search-user-link" href="/user?username=<?= rawurlencode((string) $post['username']) ?>">
                      <?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </p>

                  <h3><?= htmlspecialchars((string) ($post['title'] ?: $t->text('post_untitled')), ENT_QUOTES, 'UTF-8') ?></h3>

                  <?php $excerpt = substr((string) $post['content'], 0, 180); ?>
                  <p class="search-result-card__text">
                    <?= htmlspecialchars($excerpt . (strlen((string) $post['content']) > 180 ? '...' : ''), ENT_QUOTES, 'UTF-8') ?>
                  </p>

                  <?php if (!empty($post['image_path'])): ?>
                    <?php if (($post['media_type'] ?? 'image') === 'video'): ?>
                      <video controls preload="metadata">
                        <source src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>">
                      </video>
                    <?php else: ?>
                      <img src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('post_image_alt'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                  <?php endif; ?>

                  <a class="search-open-post" href="/post?id=<?= (int) $post['id'] ?>"><?= htmlspecialchars($t->text('search_view_post'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </section>
    <?php endif; ?>
  </main>

  <?php require dirname(__DIR__) . '/partials/app-footer.php'; ?>
</body>
</html>
