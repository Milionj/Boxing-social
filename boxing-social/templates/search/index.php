<?php require dirname(__DIR__) . '/partials/app-locale.php'; ?>
<?php
$showUsers = $scope !== 'posts';
$showPosts = $scope !== 'users';
$resultsLayoutClass = ($showUsers && $showPosts) ? '' : ' search-results--single';

$buildSearchUrl = static function (array $overrides = []) use ($query, $scope, $postTypeFilter, $usersPage, $postsPage): string {
    $params = [
        'q' => $query,
        'scope' => $scope,
        'post_type' => $postTypeFilter,
        'users_page' => $usersPage,
        'posts_page' => $postsPage,
    ];

    foreach ($overrides as $key => $value) {
        if ($value === null) {
            unset($params[$key]);
            continue;
        }

        $params[$key] = $value;
    }

    if (($params['scope'] ?? 'all') === 'users') {
        unset($params['posts_page']);
    }

    if (($params['scope'] ?? 'all') === 'posts') {
        unset($params['users_page']);
    }

    return '/search?' . http_build_query($params);
};

$excerpt = static function (string $text, int $limit = 180): string {
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    $length = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
    if ($length <= $limit) {
        return $text;
    }

    $slice = function_exists('mb_substr') ? mb_substr($text, 0, $limit) : substr($text, 0, $limit);
    return rtrim($slice) . '...';
};
?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($t->text('nav_search'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260317p">
  <link rel="stylesheet" href="/css/search-index.css?v=20260317a">
  <script src="/js/search-autocomplete.js?v=20260317a" defer></script>
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

      <div class="search-filters">
        <div class="search-filter-group">
          <span class="search-filter-group__label"><?= htmlspecialchars($t->text('search_filters_heading'), ENT_QUOTES, 'UTF-8') ?></span>
          <div class="search-filter-chips">
            <a class="search-filter-chip<?= $scope === 'all' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($buildSearchUrl(['scope' => 'all', 'users_page' => 1, 'posts_page' => 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('search_scope_all'), ENT_QUOTES, 'UTF-8') ?></a>
            <a class="search-filter-chip<?= $scope === 'users' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($buildSearchUrl(['scope' => 'users', 'users_page' => 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('search_scope_users'), ENT_QUOTES, 'UTF-8') ?></a>
            <a class="search-filter-chip<?= $scope === 'posts' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($buildSearchUrl(['scope' => 'posts', 'posts_page' => 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('search_scope_posts'), ENT_QUOTES, 'UTF-8') ?></a>
          </div>
        </div>

        <?php if ($scope !== 'users'): ?>
          <div class="search-filter-group">
            <span class="search-filter-group__label"><?= htmlspecialchars($t->text('search_post_type_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <div class="search-filter-chips">
              <a class="search-filter-chip<?= $postTypeFilter === 'all' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($buildSearchUrl(['post_type' => 'all', 'posts_page' => 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('search_post_type_all'), ENT_QUOTES, 'UTF-8') ?></a>
              <a class="search-filter-chip<?= $postTypeFilter === 'publication' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($buildSearchUrl(['post_type' => 'publication', 'posts_page' => 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></a>
              <a class="search-filter-chip<?= $postTypeFilter === 'entrainement' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($buildSearchUrl(['post_type' => 'entrainement', 'posts_page' => 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('posts_type_training'), ENT_QUOTES, 'UTF-8') ?></a>
            </div>
          </div>
        <?php endif; ?>
      </div>

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
      <section class="search-summary card">
        <div>
          <p class="search-summary__eyebrow"><?= htmlspecialchars($t->text('search_results_for'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <div class="search-summary__stats">
          <article>
            <span><?= htmlspecialchars($t->text('search_users_heading'), ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= $usersCount ?></strong>
          </article>
          <article>
            <span><?= htmlspecialchars($t->text('search_posts_heading'), ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= $postsCount ?></strong>
          </article>
        </div>
      </section>

      <section class="search-results<?= $resultsLayoutClass ?>">
        <?php if ($showUsers): ?>
          <section class="search-panel card">
            <div class="search-panel__head">
              <div>
                <h2><?= htmlspecialchars($t->text('search_users_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
              </div>
              <span class="search-panel__count"><?= $usersCount ?> <?= htmlspecialchars($t->text('search_results_suffix'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <?php if ($users === []): ?>
              <p class="search-muted"><?= htmlspecialchars($t->text('search_no_users'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
              <div class="search-grid search-grid--users">
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
                      <?= htmlspecialchars((string) (($user['bio'] ?? '') !== '' ? $excerpt((string) $user['bio'], 220) : $t->text('search_no_bio')), ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <div class="search-result-card__actions">
                      <a class="search-open-profile" href="/user?username=<?= rawurlencode((string) $user['username']) ?>"><?= htmlspecialchars($t->text('search_open_profile'), ENT_QUOTES, 'UTF-8') ?></a>
                      <form method="post" action="/friends/send" data-friend-send-form>
                        <input type="hidden" name="username" value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
                        <button
                          type="submit"
                          data-friend-send-button
                          data-label-default="<?= htmlspecialchars($t->text('search_send_request'), ENT_QUOTES, 'UTF-8') ?>"
                          data-label-sent="<?= htmlspecialchars($t->text('friends_request_sent'), ENT_QUOTES, 'UTF-8') ?>"
                        ><?= htmlspecialchars($t->text('search_send_request'), ENT_QUOTES, 'UTF-8') ?></button>
                      </form>
                    </div>
                    <p class="interaction-feedback" data-interaction-feedback hidden></p>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if ($usersCount > 0 && $usersTotalPages > 1): ?>
              <nav class="search-pagination">
                <?php if ($usersPage > 1): ?>
                  <a href="<?= htmlspecialchars($buildSearchUrl(['users_page' => $usersPage - 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('pagination_previous'), ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>

                <span><?= htmlspecialchars($t->text('pagination_label'), ENT_QUOTES, 'UTF-8') ?> <?= $usersPage ?> / <?= $usersTotalPages ?></span>

                <?php if ($usersPage < $usersTotalPages): ?>
                  <a href="<?= htmlspecialchars($buildSearchUrl(['users_page' => $usersPage + 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('pagination_next'), ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>
              </nav>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <?php if ($showPosts): ?>
          <section class="search-panel card">
            <div class="search-panel__head">
              <div>
                <h2><?= htmlspecialchars($t->text('search_posts_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
              </div>
              <span class="search-panel__count"><?= $postsCount ?> <?= htmlspecialchars($t->text('search_results_suffix'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <?php if ($posts === []): ?>
              <p class="search-muted"><?= htmlspecialchars($t->text('search_no_posts'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
              <div class="search-grid search-grid--posts">
                <?php foreach ($posts as $post): ?>
                  <?php $isTrainingPost = (($post['post_type'] ?? 'publication') === 'entrainement'); ?>
                  <article class="search-result-card search-result-card--post">
                    <?php if (!empty($post['image_path'])): ?>
                      <div class="search-post-media">
                        <?php if (($post['media_type'] ?? 'image') === 'video'): ?>
                          <video controls preload="metadata">
                            <source src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>">
                          </video>
                        <?php else: ?>
                          <img src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('post_image_alt'), ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>

                    <div class="search-post-copy">
                      <div class="search-post-topline">
                        <span class="search-post-type"><?= htmlspecialchars($isTrainingPost ? $t->text('posts_type_training') : $t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="search-post-date"><?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                      </div>

                      <p class="search-post-meta">
                        <?= htmlspecialchars($t->text('post_by'), ENT_QUOTES, 'UTF-8') ?>
                        <a class="search-user-link" href="/user?username=<?= rawurlencode((string) $post['username']) ?>">
                          <?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                      </p>

                      <h3><?= htmlspecialchars((string) (($post['title'] ?? '') !== '' ? $post['title'] : $t->text('post_untitled')), ENT_QUOTES, 'UTF-8') ?></h3>

                      <p class="search-result-card__text">
                        <?= htmlspecialchars($excerpt((string) $post['content'], 240), ENT_QUOTES, 'UTF-8') ?>
                      </p>

                      <?php if (!empty($post['location']) || !empty($post['scheduled_at'])): ?>
                        <div class="search-post-details">
                          <?php if (!empty($post['location'])): ?>
                            <span><?= htmlspecialchars($t->text('post_location'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></span>
                          <?php endif; ?>
                          <?php if (!empty($post['scheduled_at'])): ?>
                            <span><?= htmlspecialchars($t->text('training_when'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $post['scheduled_at'], ENT_QUOTES, 'UTF-8') ?></span>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>

                      <div class="search-result-card__actions">
                        <a class="search-open-post" href="/post?id=<?= (int) $post['id'] ?>"><?= htmlspecialchars($t->text('search_view_post'), ENT_QUOTES, 'UTF-8') ?></a>
                      </div>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if ($postsCount > 0 && $postsTotalPages > 1): ?>
              <nav class="search-pagination">
                <?php if ($postsPage > 1): ?>
                  <a href="<?= htmlspecialchars($buildSearchUrl(['posts_page' => $postsPage - 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('pagination_previous'), ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>

                <span><?= htmlspecialchars($t->text('pagination_label'), ENT_QUOTES, 'UTF-8') ?> <?= $postsPage ?> / <?= $postsTotalPages ?></span>

                <?php if ($postsPage < $postsTotalPages): ?>
                  <a href="<?= htmlspecialchars($buildSearchUrl(['posts_page' => $postsPage + 1]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('pagination_next'), ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>
              </nav>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </main>

  <?php require dirname(__DIR__) . '/partials/app-footer.php'; ?>
</body>
</html>
