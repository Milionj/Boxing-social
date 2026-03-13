<?php require dirname(__DIR__) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('public_profile_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/public-profile.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/templates/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <section class="hero">
      <div class="identity">
        <?php if (!empty($user['avatar_path'])): ?>
          <img class="avatar" src="<?= htmlspecialchars((string) $user['avatar_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('profile_avatar_heading'), ENT_QUOTES, 'UTF-8') ?>">
        <?php else: ?>
          <div class="avatar avatar-fallback"><?= strtoupper(substr((string) $user['username'], 0, 1)) ?></div>
        <?php endif; ?>

        <div>
          <p class="eyebrow"><?= htmlspecialchars($t->text('public_profile_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
          <h1><?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?></h1>
          <p class="meta"><?= htmlspecialchars($t->text('public_profile_member_since'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars((string) $user['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>

      <div class="actions">
        <form method="post" action="/friends/send">
          <input type="hidden" name="username" value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit"><?= htmlspecialchars($t->text('public_profile_add_friend'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
        <form method="get" action="/messages">
          <input type="hidden" name="username" value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit"><?= htmlspecialchars($t->text('public_profile_send_message'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
      </div>
    </section>

    <section class="panel">
      <h2><?= htmlspecialchars($t->text('public_profile_bio'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p><?= nl2br(htmlspecialchars((string) ($user['bio'] ?? $t->text('public_profile_bio_empty')), ENT_QUOTES, 'UTF-8')) ?></p>
    </section>

    <section class="panel">
      <div class="section-head">
        <h2><?= htmlspecialchars($t->text('public_profile_public_posts'), ENT_QUOTES, 'UTF-8') ?></h2>
        <span><?= count($posts) ?> <?= htmlspecialchars($t->text('public_profile_post_count'), ENT_QUOTES, 'UTF-8') ?></span>
      </div>

      <?php if (empty($posts)): ?>
        <p class="muted"><?= htmlspecialchars($t->text('public_profile_empty_posts'), ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <div class="posts">
          <?php foreach ($posts as $post): ?>
            <article class="post-card">
              <h3>
                <a href="/post?id=<?= (int) $post['id'] ?>">
                  <?= htmlspecialchars((string) ($post['title'] ?: $t->text('post_untitled')), ENT_QUOTES, 'UTF-8') ?>
                </a>
              </h3>
              <p><?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?></p>
              <?php if (!empty($post['image_path'])): ?>
                <img src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('post_image_alt'), ENT_QUOTES, 'UTF-8') ?>">
              <?php endif; ?>
              <?php if (!empty($post['location'])): ?>
                <p class="meta"><?= htmlspecialchars($t->text('post_location'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></p>
              <?php endif; ?>
              <p class="meta"><?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
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
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
