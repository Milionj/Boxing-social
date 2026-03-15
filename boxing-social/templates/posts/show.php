<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('post_show_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260315n">
  <link rel="stylesheet" href="/css/post-show.css?v=20260315n">
</head>
<body
  class="app-shell"
  data-post-interaction-error="<?= htmlspecialchars($t->text('posts_interaction_error'), ENT_QUOTES, 'UTF-8') ?>"
  data-comment-delete-label="<?= htmlspecialchars($t->text('post_delete_own_comment'), ENT_QUOTES, 'UTF-8') ?>"
>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <?php $isTrainingPost = (($post['post_type'] ?? 'publication') === 'entrainement'); ?>
  <main class="post-show-page app-main">
    <section class="post-show-hero">
      <h1><?= htmlspecialchars((string) ($post['title'] ?: $t->text('post_untitled')), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('post_by'), ENT_QUOTES, 'UTF-8') ?> <a href="/user?username=<?= rawurlencode((string) $post['username']) ?>"><?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?></a></p>
    </section>

    <article
      class="post-show-card <?= $isTrainingPost ? 'post-show-card--training' : 'post-show-card--publication' ?>"
      data-post-card
      data-interaction-scope
      data-post-id="<?= (int) $post['id'] ?>"
    >
      <div class="post-show-card__header">
        <span class="post-show-card__type"><?= htmlspecialchars($isTrainingPost ? $t->text('posts_type_training') : $t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></span>
        <div class="post-show-card__meta-wrap">
          <span class="post-show-card__meta"><?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
          <span class="post-show-card__visibility"><?= htmlspecialchars((string) $post['visibility'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </div>

      <?php if ($isTrainingPost): ?>
        <section class="post-show-card__training-banner">
          <div class="post-show-card__training-head">
            <p class="post-show-card__training-label"><?= htmlspecialchars($t->text('training_session_label'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>

          <div class="post-show-card__training-grid">
            <?php if (!empty($post['scheduled_at'])): ?>
              <article class="post-show-card__training-item">
                <span><?= htmlspecialchars($t->text('training_when'), ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= htmlspecialchars((string) $post['scheduled_at'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>

            <?php if (!empty($post['location'])): ?>
              <article class="post-show-card__training-item">
                <span><?= htmlspecialchars($t->text('training_where'), ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>
          </div>
        </section>
      <?php endif; ?>

      <div class="post-show-card__layout">
        <div class="post-show-card__copy">
          <div class="post-show-card__facts">
            <?php if (!$isTrainingPost && !empty($post['location'])): ?>
              <article class="post-show-card__fact-item">
                <span><?= htmlspecialchars($t->text('post_location'), ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>
            <?php if (!$isTrainingPost && !empty($post['scheduled_at'])): ?>
              <article class="post-show-card__fact-item">
                <span>Séance prévue</span>
                <strong><?= htmlspecialchars((string) $post['scheduled_at'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>
          </div>

          <div class="post-show-card__content">
            <?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?>
          </div>
        </div>

        <?php $mediaSize = htmlspecialchars((string) ($post['media_size'] ?? 'standard'), ENT_QUOTES, 'UTF-8'); ?>
        <div class="post-show-card__media post-show-card__media--<?= $mediaSize ?>">
          <?php if (!empty($post['image_path'])): ?>
            <?php if (($post['media_type'] ?? 'image') === 'video'): ?>
              <video class="hero-image" controls preload="metadata">
                <source src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>">
              </video>
            <?php else: ?>
              <img class="hero-image" src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('post_image_alt'), ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
          <?php else: ?>
            <div class="hero-image hero-image--placeholder">BOXING SOCIAL</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="post-show-card__actions">
        <div class="actions">
          <p class="post-show-card__likes"><strong><?= htmlspecialchars($t->text('posts_likes'), ENT_QUOTES, 'UTF-8') ?></strong><span data-like-count><?= $likesCount ?></span></p>
          <?php if ($currentUserId !== null): ?>
            <form method="post" action="/likes/toggle" data-like-form>
              <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
              <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
              <button
                type="submit"
                data-like-button
                data-label-default="<?= htmlspecialchars($t->text('posts_like_add'), ENT_QUOTES, 'UTF-8') ?>"
                data-label-active="<?= htmlspecialchars($t->text('posts_like_remove'), ENT_QUOTES, 'UTF-8') ?>"
              ><?= htmlspecialchars($isLiked ? $t->text('posts_like_remove') : $t->text('posts_like_add'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
          <?php endif; ?>
        </div>

        <?php if ($isTrainingPost && $currentUserId !== null && (int) $currentUserId !== (int) $post['user_id']): ?>
          <section class="post-show-card__training-cta">
            <div class="post-show-card__training-cta-copy">
              <p class="post-show-card__training-cta-title"><?= htmlspecialchars($t->text('training_interest_label'), ENT_QUOTES, 'UTF-8') ?></p>
              <p class="post-show-card__training-cta-text">
                <?= htmlspecialchars($isInterested ? $t->text('training_interest_sent') : $t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>
              </p>
            </div>

            <form method="post" action="/posts/interest" class="interest-form">
              <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
              <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
              <button type="submit" class="interest-button<?= $isInterested ? ' is-active' : '' ?>" <?= $isInterested ? 'disabled' : '' ?>>
                <span class="interest-button__icon">&#x270A;</span>
                <span class="interest-button__count"><?= (int) $interestCount ?></span>
              </button>
              <span class="interest-form__hint">
                <?= htmlspecialchars($isInterested ? $t->text('training_interest_sent') : $t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>
              </span>
            </form>
          </section>
        <?php endif; ?>
      </div>

      <p class="msg-error" data-interaction-feedback hidden></p>
    </article>

    <section class="comments-card" data-interaction-scope>
      <div class="comments-card__head">
        <div>
          <h2><?= htmlspecialchars($t->text('posts_comments'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <span class="comments-card__count" data-comment-count><?= count($comments) ?></span>
      </div>

      <?php if (!empty($successInterest)): ?>
        <p class="msg-success"><?= htmlspecialchars($successInterest, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php foreach (($errorsInterest ?? []) as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <?php if (!empty($successComments)): ?>
        <p class="msg-success"><?= htmlspecialchars($successComments, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php foreach ($errorsComments as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <?php foreach ($errorsLikes as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

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
                  <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
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
          <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
          <textarea name="content" rows="4" placeholder="<?= htmlspecialchars($t->text('posts_add_comment'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
          <button type="submit"><?= htmlspecialchars($t->text('posts_comment_button'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
      <?php endif; ?>
    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
  <script src="/js/post-interactions.js?v=20260315n" defer></script>
</body>
</html>
