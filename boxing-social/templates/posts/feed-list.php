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
    <?php $postId = (int) $post['id']; ?>
    <?php $isTrainingPost = (($post['post_type'] ?? 'publication') === 'entrainement'); ?>
    <?php $currentUserId = $_SESSION['user']['id'] ?? null; ?>
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
    <article
      class="post <?= $isTrainingPost ? 'post--training' : 'post--publication' ?> <?= $feedContext === 'home' ? 'post--home' : 'post--feed' ?> <?= $hasMedia ? 'post--with-media' : 'post--text-only' ?> <?= $contentSizeClass ?> <?= $contentDensityClass ?> post--facts-<?= $factsCount ?>"
      data-post-card
      data-interaction-scope
      data-post-id="<?= $postId ?>"
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
                <img class="post__content-brand-logo" src="<?= htmlspecialchars($siteLogoPath, ENT_QUOTES, 'UTF-8') ?>" alt="">
              </span>
            <?php endif; ?>
            <div class="post__content-text">
              <?= nl2br(htmlspecialchars($postContent, ENT_QUOTES, 'UTF-8')) ?>
            </div>
          </div>

          <?php if (($post['post_type'] ?? 'publication') === 'entrainement' && $currentUserId !== null && (int) $currentUserId !== (int) $post['user_id']): ?>
            <?php $postInterestCount = (int) ($interestCountByPost[$postId] ?? 0); ?>
            <?php $alreadyInterested = (bool) ($interestedByCurrentUser[$postId] ?? false); ?>
            <section class="post__training-cta">
              <div class="post__training-cta-copy">
                <p class="post__training-cta-title"><?= htmlspecialchars($t->text('training_interest_label'), ENT_QUOTES, 'UTF-8') ?></p>
                <p class="post__training-cta-text">
                  <?= htmlspecialchars($alreadyInterested ? $t->text('training_interest_sent') : $t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>
                </p>
              </div>

              <form method="post" action="/posts/interest" class="interest-form" data-interest-form>
                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($feedBasePath . '?page=' . (int) $currentPage, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="interest-button<?= $alreadyInterested ? ' is-active' : '' ?>" data-interest-button <?= $alreadyInterested ? 'disabled' : '' ?>>
                  <span class="interest-button__icon" aria-hidden="true">
                    <img src="/img/iconspoing.png" alt="">
                  </span>
                  <span class="interest-button__count" data-interest-count><?= $postInterestCount ?></span>
                </button>
                <span class="interest-form__hint" data-interest-hint>
                  <?= htmlspecialchars($alreadyInterested ? $t->text('training_interest_sent') : $t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>
                </span>
              </form>
            </section>
          <?php endif; ?>

          <div class="post__body-tail">
            <div class="post__actions">
              <?php $likesCount = (int) ($likesCountByPost[$postId] ?? 0); ?>
              <?php $isLiked = (bool) ($likedByCurrentUser[$postId] ?? false); ?>

              <div class="post__social">
                <p class="post__likes"><strong><?= htmlspecialchars($t->text('posts_likes'), ENT_QUOTES, 'UTF-8') ?></strong><span data-like-count><?= $likesCount ?></span></p>

                <?php if ($currentUserId !== null): ?>
                  <form method="post" action="/likes/toggle" data-like-form>
                    <input type="hidden" name="post_id" value="<?= $postId ?>">
                    <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($feedBasePath . '?page=' . (int) $currentPage, ENT_QUOTES, 'UTF-8') ?>">
                    <button
                      type="submit"
                      data-like-button
                      data-label-default="<?= htmlspecialchars($t->text('posts_like_add'), ENT_QUOTES, 'UTF-8') ?>"
                      data-label-active="<?= htmlspecialchars($t->text('posts_like_remove'), ENT_QUOTES, 'UTF-8') ?>"
                    ><?= htmlspecialchars($isLiked ? $t->text('posts_like_remove') : $t->text('posts_like_add'), ENT_QUOTES, 'UTF-8') ?></button>
                  </form>
                <?php endif; ?>
              </div>

              <?php if ($feedContext !== 'home' && $currentUserId !== null && (int) $currentUserId === (int) $post['user_id']): ?>
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

            <div class="post__footer-row">
              <details class="post-comments">
                <summary>
                  <?= htmlspecialchars($t->text('posts_comments'), ENT_QUOTES, 'UTF-8') ?>
                  <span class="post-comments__count" data-comment-count data-count-format="parentheses">(<?= count($postComments = $commentsByPost[$postId] ?? []) ?>)</span>
                </summary>

                <div class="post-comments__body">
                  <div
                    class="post-comments__list"
                    data-comment-list
                    data-empty-text="<?= htmlspecialchars($t->text('posts_no_comments'), ENT_QUOTES, 'UTF-8') ?>"
                  >
                    <?php foreach ($postComments as $comment): ?>
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
                              <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($feedBasePath . '?page=' . (int) $currentPage, ENT_QUOTES, 'UTF-8') ?>">
                              <button type="submit"><?= htmlspecialchars($t->text('posts_delete_comment'), ENT_QUOTES, 'UTF-8') ?></button>
                            </form>
                          <?php endif; ?>
                        </div>

                        <p class="comment__text"><?= nl2br(htmlspecialchars((string) $comment['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                      </article>
                    <?php endforeach; ?>
                  </div>

                  <p class="post-comments__empty" data-comment-empty <?= empty($postComments) ? '' : 'hidden' ?>>
                    <?= htmlspecialchars($t->text('posts_no_comments'), ENT_QUOTES, 'UTF-8') ?>
                  </p>

                  <?php if ($currentUserId !== null): ?>
                    <form class="post-comments__form" method="post" action="/comments" data-comment-form>
                      <input type="hidden" name="post_id" value="<?= $postId ?>">
                      <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($feedBasePath . '?page=' . (int) $currentPage, ENT_QUOTES, 'UTF-8') ?>">
                      <textarea name="content" rows="2" cols="50" placeholder="<?= htmlspecialchars($t->text('posts_add_comment'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
                      <div class="post-comments__form-actions">
                        <button type="submit"><?= htmlspecialchars($t->text('posts_comment_button'), ENT_QUOTES, 'UTF-8') ?></button>
                      </div>
                    </form>
                  <?php endif; ?>
                </div>
              </details>

              <p class="post__view-link">
                <button
                  type="button"
                  class="post__preview-toggle"
                  data-post-preview-toggle
                  aria-expanded="false"
                ><?= htmlspecialchars($t->text('posts_view'), ENT_QUOTES, 'UTF-8') ?></button>
              </p>
            </div>
          </div>
        </div>
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
