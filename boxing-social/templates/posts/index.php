<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('posts_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/posts-index.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <h1><?= htmlspecialchars($t->text('posts_heading'), ENT_QUOTES, 'UTF-8') ?></h1>

    <?php $errorsComments = $_SESSION['errors_comments'] ?? []; ?>
    <?php $successComments = $_SESSION['success_comments'] ?? ''; ?>
    <?php $errorsLikes = $_SESSION['errors_likes'] ?? []; ?>
    <?php $errorsInterest = $_SESSION['errors_posts_interest'] ?? []; ?>
    <?php $successInterest = $_SESSION['success_posts_interest'] ?? ''; ?>
    <?php unset($_SESSION['errors_comments'], $_SESSION['success_comments'], $_SESSION['errors_likes'], $_SESSION['errors_posts_interest'], $_SESSION['success_posts_interest']); ?>

    <?php if (!empty($successComments)): ?>
      <p class="msg-success"><?= htmlspecialchars($successComments, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($successInterest)): ?>
      <p class="msg-success"><?= htmlspecialchars($successInterest, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errorsComments)): ?>
      <?php foreach ($errorsComments as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($errorsLikes)): ?>
      <?php foreach ($errorsLikes as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($errorsInterest)): ?>
      <?php foreach ($errorsInterest as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (empty($feed)): ?>
      <p><?= htmlspecialchars($t->text('posts_empty'), ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
      <?php foreach ($feed as $post): ?>
        <article class="post">
          <p><strong>Type:</strong> <?= (($post['post_type'] ?? 'publication') === 'entrainement') ? 'Seance d entrainement' : 'Publication simple' ?></p>
          <h3><?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
          <p>
            <strong><?= htmlspecialchars($t->text('posts_author'), ENT_QUOTES, 'UTF-8') ?>:</strong>
            <a href="/user?username=<?= rawurlencode((string) $post['username']) ?>">
              <?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </p>
          <p><?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?></p>

          <?php if (!empty($post['image_path'])): ?>
            <p><img class="post-image" src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('post_image_alt'), ENT_QUOTES, 'UTF-8') ?>"></p>
          <?php endif; ?>

          <?php if (!empty($post['location'])): ?>
            <p><strong><?= htmlspecialchars($t->text('post_location'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>

          <?php if (!empty($post['scheduled_at'])): ?>
            <p><strong>Seance prevue:</strong> <?= htmlspecialchars((string) $post['scheduled_at'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>

          <?php $currentUserId = $_SESSION['user']['id'] ?? null; ?>
          <?php if (($post['post_type'] ?? 'publication') === 'entrainement' && $currentUserId !== null && (int) $currentUserId !== (int) $post['user_id']): ?>
            <?php $postInterestCount = (int) ($interestCountByPost[(int) $post['id']] ?? 0); ?>
            <?php $alreadyInterested = (bool) ($interestedByCurrentUser[(int) $post['id']] ?? false); ?>
            <form method="post" action="/posts/interest" class="interest-form">
              <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
              <input type="hidden" name="redirect_to" value="/posts?page=<?= (int) $currentPage ?>">
              <button type="submit" class="interest-button<?= $alreadyInterested ? ' is-active' : '' ?>" <?= $alreadyInterested ? 'disabled' : '' ?>>
                <span class="interest-button__icon">&#x270A;</span>
                <span class="interest-button__count"><?= $postInterestCount ?></span>
              </button>
              <span class="interest-form__hint">
                <?= $alreadyInterested ? 'Interet deja envoye' : 'Cliquer sur le poing pour manifester votre interet' ?>
              </span>
            </form>
          <?php endif; ?>

          <?php if ($currentUserId !== null && (int) $currentUserId === (int) $post['user_id']): ?>
            <p><a href="/posts/edit?id=<?= (int) $post['id'] ?>"><?= htmlspecialchars($t->text('posts_edit'), ENT_QUOTES, 'UTF-8') ?></a></p>
            <form method="post" action="/posts/delete" onsubmit="return confirm('<?= htmlspecialchars($t->text('posts_delete_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
              <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
              <button type="submit"><?= htmlspecialchars($t->text('posts_delete'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
          <?php endif; ?>

          <p><small><?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string) $post['visibility'], ENT_QUOTES, 'UTF-8') ?></small></p>

          <hr>
          <?php $postId = (int) $post['id']; ?>
          <?php $likesCount = (int) ($likesCountByPost[$postId] ?? 0); ?>
          <?php $isLiked = (bool) ($likedByCurrentUser[$postId] ?? false); ?>

          <p><strong><?= htmlspecialchars($t->text('posts_likes'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= $likesCount ?></p>

          <?php if ($currentUserId !== null): ?>
            <form method="post" action="/likes/toggle">
              <input type="hidden" name="post_id" value="<?= $postId ?>">
              <input type="hidden" name="redirect_to" value="/posts?page=<?= (int) $currentPage ?>">
              <button type="submit"><?= htmlspecialchars($isLiked ? $t->text('posts_like_remove') : $t->text('posts_like_add'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
          <?php endif; ?>

          <hr>
          <h4><?= htmlspecialchars($t->text('posts_comments'), ENT_QUOTES, 'UTF-8') ?></h4>

          <?php $postComments = $commentsByPost[(int) $post['id']] ?? []; ?>
          <?php if (empty($postComments)): ?>
            <p><?= htmlspecialchars($t->text('posts_no_comments'), ENT_QUOTES, 'UTF-8') ?></p>
          <?php else: ?>
            <?php foreach ($postComments as $comment): ?>
              <div class="comment">
                <p>
                  <strong><?= htmlspecialchars((string) $comment['username'], ENT_QUOTES, 'UTF-8') ?>:</strong>
                  <?= nl2br(htmlspecialchars((string) $comment['content'], ENT_QUOTES, 'UTF-8')) ?>
                </p>
                <small><?= htmlspecialchars((string) $comment['created_at'], ENT_QUOTES, 'UTF-8') ?></small>

                <?php if ($currentUserId !== null && (int) $currentUserId === (int) $comment['user_id']): ?>
                  <form class="form-inline" method="post" action="/comments/delete">
                    <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                    <input type="hidden" name="redirect_to" value="/posts?page=<?= (int) $currentPage ?>">
                    <button type="submit"><?= htmlspecialchars($t->text('posts_delete_comment'), ENT_QUOTES, 'UTF-8') ?></button>
                  </form>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php if ($currentUserId !== null): ?>
            <form method="post" action="/comments">
              <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
              <input type="hidden" name="redirect_to" value="/posts?page=<?= (int) $currentPage ?>">
              <textarea name="content" rows="2" cols="50" placeholder="<?= htmlspecialchars($t->text('posts_add_comment'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
              <br>
              <button type="submit"><?= htmlspecialchars($t->text('posts_comment_button'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
          <?php endif; ?>

          <p><a href="/post?id=<?= (int) $post['id'] ?>"><?= htmlspecialchars($t->text('posts_view'), ENT_QUOTES, 'UTF-8') ?></a></p>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
      <nav class="pagination">
        <?php if ($currentPage > 1): ?>
          <a href="/posts?page=<?= $currentPage - 1 ?>"><?= htmlspecialchars($t->text('pagination_previous'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>

        <span><?= htmlspecialchars($t->text('pagination_label'), ENT_QUOTES, 'UTF-8') ?> <?= $currentPage ?> / <?= $totalPages ?></span>

        <?php if ($currentPage < $totalPages): ?>
          <a href="/posts?page=<?= $currentPage + 1 ?>"><?= htmlspecialchars($t->text('pagination_next'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
