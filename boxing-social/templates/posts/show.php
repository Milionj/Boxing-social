<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('post_show_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260315o">
  <link rel="stylesheet" href="/css/posts-index.css?v=20260317b">
  <link rel="stylesheet" href="/css/post-show.css?v=20260317b">
</head>
<body
  class="app-shell"
  data-post-interaction-error="<?= htmlspecialchars($t->text('posts_interaction_error'), ENT_QUOTES, 'UTF-8') ?>"
  data-comment-delete-label="<?= htmlspecialchars($t->text('posts_delete_comment'), ENT_QUOTES, 'UTF-8') ?>"
>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <?php $isTrainingPost = (($post['post_type'] ?? 'publication') === 'entrainement'); ?>
  <?php $postContent = trim((string) ($post['content'] ?? '')); ?>
  <?php $contentLength = function_exists('mb_strlen') ? mb_strlen($postContent) : strlen($postContent); ?>
  <?php $hasMedia = !empty($post['image_path']); ?>
  <?php $hasTrainingDate = !empty($post['scheduled_at']); ?>
  <?php $hasTrainingLocation = !empty($post['location']); ?>
  <?php $trainingDetailsCount = ($hasTrainingDate ? 1 : 0) + ($hasTrainingLocation ? 1 : 0); ?>
  <?php $hasFactLocation = !$isTrainingPost && $hasTrainingLocation; ?>
  <?php $hasFactDate = !$isTrainingPost && $hasTrainingDate; ?>
  <?php $factsCount = ($hasFactLocation ? 1 : 0) + ($hasFactDate ? 1 : 0); ?>
  <?php $contentSizeClass = $contentLength <= 120 ? 'post--content-short' : ($contentLength >= 360 ? 'post--content-long' : 'post--content-medium'); ?>
  <?php $contentDensityClass = $contentLength <= 55 ? 'post--content-very-short' : ''; ?>
  <?php $showBasePath = '/post?id=' . (int) $post['id']; ?>

  <main class="post-show-page posts-page app-main">
    <section class="posts-hero post-show-hero">
      <p class="posts-hero__eyebrow"><?= htmlspecialchars($isTrainingPost ? $t->text('posts_type_training') : $t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></p>
      <h1><?= htmlspecialchars((string) (($post['title'] ?? '') !== '' ? $post['title'] : $t->text('post_untitled')), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('post_by'), ENT_QUOTES, 'UTF-8') ?> <a href="/user?username=<?= rawurlencode((string) $post['username']) ?>"><?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?></a></p>
    </section>

    <?php if (!empty($successComments)): ?>
      <p class="msg-success"><?= htmlspecialchars($successComments, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($successInterest)): ?>
      <p class="msg-success"><?= htmlspecialchars($successInterest, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php foreach (($errorsComments ?? []) as $error): ?>
      <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>

    <?php foreach (($errorsLikes ?? []) as $error): ?>
      <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>

    <?php foreach (($errorsInterest ?? []) as $error): ?>
      <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>

    <article
      class="post <?= $isTrainingPost ? 'post--training' : 'post--publication' ?> post--detail <?= $hasMedia ? 'post--with-media' : 'post--text-only' ?> <?= $contentSizeClass ?> <?= $contentDensityClass ?> post--facts-<?= $factsCount ?>"
      data-post-card
      data-interaction-scope
      data-post-id="<?= (int) $post['id'] ?>"
    >
      <div class="post__type-row">
        <p class="post__type"><?= htmlspecialchars($isTrainingPost ? $t->text('posts_type_training') : $t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <?php if ($isTrainingPost && $trainingDetailsCount > 0): ?>
        <section class="post__training-banner">
          <div class="post__training-banner-head">
            <p class="post__training-label"><?= htmlspecialchars($t->text('training_session_label'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>

          <div class="post__training-grid">
            <?php if ($hasTrainingDate): ?>
              <article class="post__training-card">
                <span><?= htmlspecialchars($t->text('training_when'), ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= htmlspecialchars((string) $post['scheduled_at'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>

            <?php if ($hasTrainingLocation): ?>
              <article class="post__training-card">
                <span><?= htmlspecialchars($t->text('training_where'), ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>
          </div>
        </section>
      <?php endif; ?>

      <div class="post__layout<?= $hasMedia ? '' : ' post__layout--single' ?>">
        <div class="post__body">
          <?php if ($factsCount > 0): ?>
            <div class="post__facts">
              <?php if ($hasFactLocation): ?>
                <article class="post__fact-card">
                  <span><?= htmlspecialchars($t->text('post_location'), ENT_QUOTES, 'UTF-8') ?></span>
                  <strong><?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></strong>
                </article>
              <?php endif; ?>

              <?php if ($hasFactDate): ?>
                <article class="post__fact-card">
                  <span>Séance prévue</span>
                  <strong><?= htmlspecialchars((string) $post['scheduled_at'], ENT_QUOTES, 'UTF-8') ?></strong>
                </article>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if ($hasMedia): ?>
            <?php $mediaSize = htmlspecialchars((string) ($post['media_size'] ?? 'standard'), ENT_QUOTES, 'UTF-8'); ?>
            <?php $mediaType = htmlspecialchars((string) ($post['media_type'] ?? 'image'), ENT_QUOTES, 'UTF-8'); ?>
            <div class="post__media post__media--<?= $mediaSize ?> post__media--type-<?= $mediaType ?>">
              <?php if (($post['media_type'] ?? 'image') === 'video'): ?>
                <video class="post-image" controls preload="metadata">
                  <source src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>">
                </video>
              <?php else: ?>
                <img class="post-image" src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('post_image_alt'), ENT_QUOTES, 'UTF-8') ?>">
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="post__head">
            <p class="post__stamp"><?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?></p>

            <h3 class="post__title"><?= htmlspecialchars((string) (($post['title'] ?? '') !== '' ? $post['title'] : $t->text('post_untitled')), ENT_QUOTES, 'UTF-8') ?></h3>

            <div class="post__identity">
              <p class="post__author">
                <strong><?= htmlspecialchars($t->text('posts_author'), ENT_QUOTES, 'UTF-8') ?> :</strong>
                <a href="/user?username=<?= rawurlencode((string) $post['username']) ?>">
                  <?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </p>

              <p class="post__visibility-chip"><?= htmlspecialchars((string) $post['visibility'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
          </div>

          <div class="post__content<?= $hasMedia ? '' : ' post__content--no-media' ?>">
            <?php if (!$hasMedia): ?>
              <span class="post__content-brand" aria-hidden="true">
                <img class="post__content-brand-logo" src="/img/Bonlogo.png" alt="">
              </span>
            <?php endif; ?>
            <div class="post__content-text">
              <?= nl2br(htmlspecialchars($postContent, ENT_QUOTES, 'UTF-8')) ?>
            </div>
          </div>

          <?php if ($isTrainingPost && $currentUserId !== null && (int) $currentUserId !== (int) $post['user_id']): ?>
            <section class="post__training-cta">
              <div class="post__training-cta-copy">
                <p class="post__training-cta-title"><?= htmlspecialchars($t->text('training_interest_label'), ENT_QUOTES, 'UTF-8') ?></p>
                <p class="post__training-cta-text">
                  <?= htmlspecialchars($isInterested ? $t->text('training_interest_sent') : $t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>
                </p>
              </div>

              <form method="post" action="/posts/interest" class="interest-form" data-interest-form>
                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($showBasePath, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="interest-button<?= $isInterested ? ' is-active' : '' ?>" data-interest-button <?= $isInterested ? 'disabled' : '' ?>>
                  <span class="interest-button__icon" aria-hidden="true">
                    <img src="/img/iconspoing.png" alt="">
                  </span>
                  <span class="interest-button__count" data-interest-count><?= (int) $interestCount ?></span>
                </button>
                <span class="interest-form__hint" data-interest-hint>
                  <?= htmlspecialchars($isInterested ? $t->text('training_interest_sent') : $t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>
                </span>
              </form>
            </section>
          <?php endif; ?>

          <div class="post__body-tail">
            <div class="post__actions">
              <div class="post__social">
                <p class="post__likes"><strong><?= htmlspecialchars($t->text('posts_likes'), ENT_QUOTES, 'UTF-8') ?></strong><span data-like-count><?= $likesCount ?></span></p>

                <?php if ($currentUserId !== null): ?>
                  <form method="post" action="/likes/toggle" data-like-form>
                    <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                    <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($showBasePath, ENT_QUOTES, 'UTF-8') ?>">
                    <button
                      type="submit"
                      data-like-button
                      data-label-default="<?= htmlspecialchars($t->text('posts_like_add'), ENT_QUOTES, 'UTF-8') ?>"
                      data-label-active="<?= htmlspecialchars($t->text('posts_like_remove'), ENT_QUOTES, 'UTF-8') ?>"
                    ><?= htmlspecialchars($isLiked ? $t->text('posts_like_remove') : $t->text('posts_like_add'), ENT_QUOTES, 'UTF-8') ?></button>
                  </form>
                <?php endif; ?>
              </div>

              <?php if ($currentUserId !== null && (int) $currentUserId === (int) $post['user_id']): ?>
                <div class="post__owner-actions">
                  <p><a href="/posts/edit?id=<?= (int) $post['id'] ?>"><?= htmlspecialchars($t->text('posts_edit'), ENT_QUOTES, 'UTF-8') ?></a></p>
                  <form method="post" action="/posts/delete" onsubmit="return confirm('<?= htmlspecialchars($t->text('posts_delete_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                    <button type="submit"><?= htmlspecialchars($t->text('posts_delete'), ENT_QUOTES, 'UTF-8') ?></button>
                  </form>
                </div>
              <?php endif; ?>
            </div>

            <p class="msg-error" data-interaction-feedback hidden></p>

            <div class="post__footer-row post-show__footer-row">
              <p class="post__likes post-show__comments-chip"><strong><?= htmlspecialchars($t->text('posts_comments'), ENT_QUOTES, 'UTF-8') ?></strong><span data-comment-count><?= count($comments) ?></span></p>
              <p class="post__view-link"><a href="#post-comments" class="post-show__jump-link"><?= htmlspecialchars($t->text('posts_comments'), ENT_QUOTES, 'UTF-8') ?></a></p>
            </div>
          </div>
        </div>
      </div>
    </article>

    <section class="comments-card" id="post-comments" data-interaction-scope>
      <div class="comments-card__head">
        <div>
          <p class="comments-card__eyebrow"><?= htmlspecialchars($t->text('posts_comments'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($t->text('posts_comments'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <span class="comments-card__count" data-comment-count><?= count($comments) ?></span>
      </div>

      <p class="msg-error" data-interaction-feedback hidden></p>

      <div
        class="comments-list"
        data-comment-list
        data-empty-text="<?= htmlspecialchars($t->text('post_no_comments_yet'), ENT_QUOTES, 'UTF-8') ?>"
      >
        <?php foreach ($comments as $comment): ?>
          <article class="comment" data-comment-id="<?= (int) $comment['id'] ?>">
            <div class="comment__meta">
              <div class="comment__authorline">
                <strong>
                  <a href="/user?username=<?= rawurlencode((string) $comment['username']) ?>">
                    <?= htmlspecialchars((string) $comment['username'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </strong>
                <small><?= htmlspecialchars((string) $comment['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
              </div>

              <?php if ($currentUserId !== null && (int) $currentUserId === (int) $comment['user_id']): ?>
                <form class="comment__delete" method="post" action="/comments/delete" data-comment-delete-form>
                  <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                  <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($showBasePath, ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit"><?= htmlspecialchars($t->text('post_delete_own_comment'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
              <?php endif; ?>
            </div>

            <p class="comment__text"><?= nl2br(htmlspecialchars((string) $comment['content'], ENT_QUOTES, 'UTF-8')) ?></p>
          </article>
        <?php endforeach; ?>
      </div>

      <p class="muted" data-comment-empty <?= empty($comments) ? '' : 'hidden' ?>>
        <?= htmlspecialchars($t->text('post_no_comments_yet'), ENT_QUOTES, 'UTF-8') ?>
      </p>

      <?php if ($currentUserId !== null): ?>
        <form class="comment-form" method="post" action="/comments" data-comment-form>
          <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
          <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($showBasePath, ENT_QUOTES, 'UTF-8') ?>">
          <textarea name="content" rows="4" placeholder="<?= htmlspecialchars($t->text('posts_add_comment'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
          <button type="submit"><?= htmlspecialchars($t->text('posts_comment_button'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
      <?php endif; ?>
    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
  <script src="/js/post-interactions.js?v=20260317g" defer></script>
</body>
</html>
