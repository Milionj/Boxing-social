<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('post_show_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/post-show.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <article class="post-card">
      <p class="meta"><strong>Type :</strong> <?= (($post['post_type'] ?? 'publication') === 'entrainement') ? 'Seance d entrainement' : 'Publication simple' ?></p>
      <p class="meta">
        <?= htmlspecialchars($t->text('post_by'), ENT_QUOTES, 'UTF-8') ?>
        <a href="/user?username=<?= rawurlencode((string) $post['username']) ?>">
          <?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?>
        </a>
      </p>
      <h1><?= htmlspecialchars((string) ($post['title'] ?: $t->text('post_untitled')), ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="content"><?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?></p>

      <?php if (!empty($post['image_path'])): ?>
        <img class="hero-image" src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('post_image_alt'), ENT_QUOTES, 'UTF-8') ?>">
      <?php endif; ?>

      <?php if (!empty($post['location'])): ?>
        <p class="meta"><?= htmlspecialchars($t->text('post_location'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if (!empty($post['scheduled_at'])): ?>
        <p class="meta"><strong>Seance prevue :</strong> <?= htmlspecialchars((string) $post['scheduled_at'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <p class="meta">
        <?= htmlspecialchars($t->text('post_created_at'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?>
        | <?= htmlspecialchars($t->text('post_visibility'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $post['visibility'], ENT_QUOTES, 'UTF-8') ?>
      </p>

      <div class="actions">
        <p><strong><?= htmlspecialchars($t->text('posts_likes'), ENT_QUOTES, 'UTF-8') ?> :</strong> <?= $likesCount ?></p>
        <?php if ($currentUserId !== null): ?>
          <form method="post" action="/likes/toggle">
            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
            <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
            <button type="submit"><?= htmlspecialchars($isLiked ? $t->text('posts_like_remove') : $t->text('posts_like_add'), ENT_QUOTES, 'UTF-8') ?></button>
          </form>
        <?php endif; ?>
      </div>
    </article>

    <section class="comments-card">
      <?php if (!empty($successInterest)): ?>
        <p class="msg-success"><?= htmlspecialchars($successInterest, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php foreach (($errorsInterest ?? []) as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <?php if (($post['post_type'] ?? 'publication') === 'entrainement' && $currentUserId !== null && (int) $currentUserId !== (int) $post['user_id']): ?>
        <form method="post" action="/posts/interest" class="interest-form">
          <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
          <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
          <button type="submit" class="interest-button<?= $isInterested ? ' is-active' : '' ?>" <?= $isInterested ? 'disabled' : '' ?>>
            <span class="interest-button__icon">&#x270A;</span>
            <span class="interest-button__count"><?= (int) $interestCount ?></span>
          </button>
          <span class="interest-form__hint">
            <?= $isInterested ? 'Interet deja envoye' : 'Cliquer sur le poing pour manifester votre interet' ?>
          </span>
        </form>
      <?php endif; ?>

      <h2><?= htmlspecialchars($t->text('posts_comments'), ENT_QUOTES, 'UTF-8') ?></h2>

      <?php if (!empty($successComments)): ?>
        <p class="msg-success"><?= htmlspecialchars($successComments, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php foreach ($errorsComments as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <?php foreach ($errorsLikes as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <?php if (empty($comments)): ?>
        <p class="muted"><?= htmlspecialchars($t->text('post_no_comments_yet'), ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <div class="comment">
            <p>
              <strong>
                <a href="/user?username=<?= rawurlencode((string) $comment['username']) ?>">
                  <?= htmlspecialchars((string) $comment['username'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </strong>
              <?= nl2br(htmlspecialchars((string) $comment['content'], ENT_QUOTES, 'UTF-8')) ?>
            </p>
            <small><?= htmlspecialchars((string) $comment['created_at'], ENT_QUOTES, 'UTF-8') ?></small>

            <?php if ($currentUserId !== null && (int) $currentUserId === (int) $comment['user_id']): ?>
              <form method="post" action="/comments/delete">
                <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
                <button type="submit"><?= htmlspecialchars($t->text('post_delete_own_comment'), ENT_QUOTES, 'UTF-8') ?></button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if ($currentUserId !== null): ?>
        <form class="comment-form" method="post" action="/comments">
          <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
          <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
          <textarea name="content" rows="4" placeholder="<?= htmlspecialchars($t->text('posts_add_comment'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
          <button type="submit"><?= htmlspecialchars($t->text('posts_comment_button'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
      <?php endif; ?>
    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
