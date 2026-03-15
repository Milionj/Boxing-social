<?php
declare(strict_types=1);

$feedBasePath = $feedBasePath ?? '/posts';
$feedContext = $feedContext ?? 'posts';
$siteLogoPath = '/img/Bonlogo.png';
?>
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
    <?php $isTrainingPost = (($post['post_type'] ?? 'publication') === 'entrainement'); ?>
    <article class="post <?= $isTrainingPost ? 'post--training' : 'post--publication' ?> <?= $feedContext === 'home' ? 'post--home' : 'post--feed' ?>">
      <div class="post__head">
        <div class="post__topline">
          <p class="post__type"><?= htmlspecialchars($isTrainingPost ? $t->text('posts_type_training') : $t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></p>
          <p class="post__stamp"><?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

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

      <?php if ($isTrainingPost): ?>
        <section class="post__training-banner">
          <div class="post__training-banner-head">
            <p class="post__training-label"><?= htmlspecialchars($t->text('training_session_label'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>

          <div class="post__training-grid">
            <?php if (!empty($post['scheduled_at'])): ?>
              <article class="post__training-card">
                <span><?= htmlspecialchars($t->text('training_when'), ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= htmlspecialchars((string) $post['scheduled_at'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>

            <?php if (!empty($post['location'])): ?>
              <article class="post__training-card">
                <span><?= htmlspecialchars($t->text('training_where'), ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>
          </div>
        </section>
      <?php endif; ?>

      <div class="post__layout">
        <div class="post__body">
          <div class="post__facts">
            <?php if (!$isTrainingPost && !empty($post['location'])): ?>
              <article class="post__fact-card">
                <span><?= htmlspecialchars($t->text('post_location'), ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>

            <?php if (!$isTrainingPost && !empty($post['scheduled_at'])): ?>
              <article class="post__fact-card">
                <span>Séance prévue</span>
                <strong><?= htmlspecialchars((string) $post['scheduled_at'], ENT_QUOTES, 'UTF-8') ?></strong>
              </article>
            <?php endif; ?>
          </div>

          <div class="post__content">
            <?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?>
          </div>
        </div>

        <?php $mediaSize = htmlspecialchars((string) ($post['media_size'] ?? 'standard'), ENT_QUOTES, 'UTF-8'); ?>
        <div class="post__media post__media--<?= $mediaSize ?>">
          <?php if (!empty($post['image_path'])): ?>
            <?php if (($post['media_type'] ?? 'image') === 'video'): ?>
              <video class="post-image" controls preload="metadata">
                <source src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>">
              </video>
            <?php else: ?>
              <img class="post-image" src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('post_image_alt'), ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
          <?php else: ?>
            <div class="post-image post-image--placeholder">
              <img class="post-image__logo" src="<?= htmlspecialchars($siteLogoPath, ENT_QUOTES, 'UTF-8') ?>" alt="Logo Boxing Social">
            </div>
          <?php endif; ?>
        </div>
      </div>

      <?php $currentUserId = $_SESSION['user']['id'] ?? null; ?>
      <?php if (($post['post_type'] ?? 'publication') === 'entrainement' && $currentUserId !== null && (int) $currentUserId !== (int) $post['user_id']): ?>
        <?php $postInterestCount = (int) ($interestCountByPost[(int) $post['id']] ?? 0); ?>
        <?php $alreadyInterested = (bool) ($interestedByCurrentUser[(int) $post['id']] ?? false); ?>
        <section class="post__training-cta">
          <div class="post__training-cta-copy">
            <p class="post__training-cta-title"><?= htmlspecialchars($t->text('training_interest_label'), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="post__training-cta-text">
              <?= htmlspecialchars($alreadyInterested ? $t->text('training_interest_sent') : $t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>

          <form method="post" action="/posts/interest" class="interest-form">
            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($feedBasePath . '?page=' . (int) $currentPage, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="interest-button<?= $alreadyInterested ? ' is-active' : '' ?>" <?= $alreadyInterested ? 'disabled' : '' ?>>
              <span class="interest-button__icon">&#x270A;</span>
              <span class="interest-button__count"><?= $postInterestCount ?></span>
            </button>
            <span class="interest-form__hint">
              <?= htmlspecialchars($alreadyInterested ? $t->text('training_interest_sent') : $t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>
            </span>
          </form>
        </section>
      <?php endif; ?>

      <div class="post__actions">
        <?php $postId = (int) $post['id']; ?>
        <?php $likesCount = (int) ($likesCountByPost[$postId] ?? 0); ?>
        <?php $isLiked = (bool) ($likedByCurrentUser[$postId] ?? false); ?>

        <div class="post__social">
          <p class="post__likes"><strong><?= htmlspecialchars($t->text('posts_likes'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= $likesCount ?></span></p>

          <?php if ($currentUserId !== null): ?>
            <form method="post" action="/likes/toggle">
              <input type="hidden" name="post_id" value="<?= $postId ?>">
              <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($feedBasePath . '?page=' . (int) $currentPage, ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit"><?= htmlspecialchars($isLiked ? $t->text('posts_like_remove') : $t->text('posts_like_add'), ENT_QUOTES, 'UTF-8') ?></button>
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

      <div class="post__footer-row">
        <details class="post-comments">
          <summary>
            <?= htmlspecialchars($t->text('posts_comments'), ENT_QUOTES, 'UTF-8') ?>
            <span class="post-comments__count">(<?= count($postComments = $commentsByPost[(int) $post['id']] ?? []) ?>)</span>
          </summary>

          <div class="post-comments__body">
            <?php if (empty($postComments)): ?>
              <p class="post-comments__empty"><?= htmlspecialchars($t->text('posts_no_comments'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
              <?php foreach ($postComments as $comment): ?>
                <article class="comment">
                  <div class="comment__meta">
                    <div class="comment__authorline">
                      <strong><?= htmlspecialchars((string) $comment['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                      <small><?= htmlspecialchars((string) $comment['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
                    </div>

                    <?php if ($currentUserId !== null && (int) $currentUserId === (int) $comment['user_id']): ?>
                      <form class="form-inline comment__delete" method="post" action="/comments/delete">
                        <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($feedBasePath . '?page=' . (int) $currentPage, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit"><?= htmlspecialchars($t->text('posts_delete_comment'), ENT_QUOTES, 'UTF-8') ?></button>
                      </form>
                    <?php endif; ?>
                  </div>

                  <p class="comment__text"><?= nl2br(htmlspecialchars((string) $comment['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($currentUserId !== null): ?>
              <form class="post-comments__form" method="post" action="/comments">
                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($feedBasePath . '?page=' . (int) $currentPage, ENT_QUOTES, 'UTF-8') ?>">
                <textarea name="content" rows="2" cols="50" placeholder="<?= htmlspecialchars($t->text('posts_add_comment'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
                <div class="post-comments__form-actions">
                  <button type="submit"><?= htmlspecialchars($t->text('posts_comment_button'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
              </form>
            <?php endif; ?>
          </div>
        </details>

        <p class="post__view-link"><a href="/post?id=<?= (int) $post['id'] ?>"><?= htmlspecialchars($t->text('posts_view'), ENT_QUOTES, 'UTF-8') ?></a></p>
      </div>
    </article>
  <?php endforeach; ?>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
  <nav class="pagination">
    <?php if ($currentPage > 1): ?>
      <a href="<?= htmlspecialchars($feedBasePath . '?page=' . ($currentPage - 1), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('pagination_previous'), ENT_QUOTES, 'UTF-8') ?></a>
    <?php endif; ?>

    <span><?= htmlspecialchars($t->text('pagination_label'), ENT_QUOTES, 'UTF-8') ?> <?= $currentPage ?> / <?= $totalPages ?></span>

    <?php if ($currentPage < $totalPages): ?>
      <a href="<?= htmlspecialchars($feedBasePath . '?page=' . ($currentPage + 1), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('pagination_next'), ENT_QUOTES, 'UTF-8') ?></a>
    <?php endif; ?>
  </nav>
<?php endif; ?>
