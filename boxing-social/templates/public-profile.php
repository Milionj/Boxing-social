<?php require dirname(__DIR__) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('public_profile_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260317p">
  <link rel="stylesheet" href="/css/public-profile.css?v=20260315o">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/templates/partials/app-navbar.php'; ?>
  <main class="public-profile-page app-main">
    <section class="public-profile-hero">
      <h1><?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('public_profile_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <div class="public-profile-layout">
      <aside class="public-profile-summary card">
        <div class="public-profile-summary__avatar-wrap">
          <?php if (!empty($user['avatar_path'])): ?>
            <img class="public-profile-summary__avatar" src="<?= htmlspecialchars((string) $user['avatar_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('profile_avatar_alt'), ENT_QUOTES, 'UTF-8') ?>">
          <?php else: ?>
            <div class="public-profile-summary__avatar public-profile-summary__avatar--fallback"><?= htmlspecialchars(strtoupper(substr((string) $user['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
        </div>

        <div class="public-profile-summary__identity">
          <h2><?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="public-profile-summary__meta"><?= htmlspecialchars($t->text('public_profile_member_since'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $user['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="public-profile-summary__actions" data-social-scope>
          <form method="post" action="/friends/send" data-friend-send-form>
            <input type="hidden" name="username" value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
            <button
              type="submit"
              data-friend-send-button
              data-label-default="<?= htmlspecialchars($t->text('public_profile_add_friend'), ENT_QUOTES, 'UTF-8') ?>"
              data-label-sent="<?= htmlspecialchars($t->text('friends_request_sent'), ENT_QUOTES, 'UTF-8') ?>"
            ><?= htmlspecialchars($t->text('public_profile_add_friend'), ENT_QUOTES, 'UTF-8') ?></button>
          </form>
          <form method="get" action="/messages">
            <input type="hidden" name="username" value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
            <button class="button-secondary" type="submit"><?= htmlspecialchars($t->text('public_profile_send_message'), ENT_QUOTES, 'UTF-8') ?></button>
          </form>
          <p class="interaction-feedback" data-interaction-feedback hidden></p>
        </div>
      </aside>

      <div class="public-profile-stack">
        <section class="card public-profile-panel">
          <div class="public-profile-panel__head">
            <div>
              <h2><?= htmlspecialchars($t->text('public_profile_bio'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
          </div>
          <p class="public-profile-panel__text">
            <?= nl2br(htmlspecialchars((string) (($user['bio'] ?? '') !== '' ? $user['bio'] : $t->text('public_profile_bio_empty')), ENT_QUOTES, 'UTF-8')) ?>
          </p>
        </section>

        <section class="card public-profile-panel">
          <div class="public-profile-panel__head">
            <div>
              <h2><?= htmlspecialchars($t->text('public_profile_public_posts'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <span class="public-profile-panel__count"><?= count($posts) ?> <?= htmlspecialchars($t->text('public_profile_post_count'), ENT_QUOTES, 'UTF-8') ?></span>
          </div>

          <?php if (empty($posts)): ?>
            <p class="public-profile-empty"><?= htmlspecialchars($t->text('public_profile_empty_posts'), ENT_QUOTES, 'UTF-8') ?></p>
          <?php else: ?>
            <div class="public-profile-posts">
              <?php foreach ($posts as $post): ?>
                <article class="public-post-card">
                  <div class="public-post-card__copy">
                    <h3>
                      <a href="/post?id=<?= (int) $post['id'] ?>">
                        <?= htmlspecialchars((string) ($post['title'] ?: $t->text('post_untitled')), ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    </h3>
                    <p><?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php if (!empty($post['location'])): ?>
                      <p class="public-post-card__meta"><?= htmlspecialchars($t->text('post_location'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <p class="public-post-card__meta"><?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                  </div>
                  <?php if (!empty($post['image_path'])): ?>
                    <?php if (($post['media_type'] ?? 'image') === 'video'): ?>
                      <video controls preload="metadata">
                        <source src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>">
                      </video>
                    <?php else: ?>
                      <img src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('post_image_alt'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($totalPages > 1): ?>
            <nav class="pagination">
              <?php if ($currentPage > 1): ?>
                <a href="/user?username=<?= rawurlencode((string) $user['username']) ?>&page=<?= $currentPage - 1 ?>"><?= htmlspecialchars($t->text('pagination_previous'), ENT_QUOTES, 'UTF-8') ?></a>
              <?php endif; ?>

              <span><?= htmlspecialchars($t->text('pagination_label'), ENT_QUOTES, 'UTF-8') ?> <?= $currentPage ?> / <?= $totalPages ?></span>

              <?php if ($currentPage < $totalPages): ?>
                <a href="/user?username=<?= rawurlencode((string) $user['username']) ?>&page=<?= $currentPage + 1 ?>"><?= htmlspecialchars($t->text('pagination_next'), ENT_QUOTES, 'UTF-8') ?></a>
              <?php endif; ?>
            </nav>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
