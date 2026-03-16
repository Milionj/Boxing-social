<?php
declare(strict_types=1);

if (!isset($t) || !$t instanceof \App\Services\Translator) {
    require __DIR__ . '/app-locale.php';
}
?>
<div
  hidden
  data-social-i18n
  data-error-generic="<?= htmlspecialchars($t->text('interaction_generic_error'), ENT_QUOTES, 'UTF-8') ?>"
  data-friend-request-sent="<?= htmlspecialchars($t->text('friends_request_sent'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-open-profile="<?= htmlspecialchars($t->text('friends_open_profile'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-pending-with="<?= htmlspecialchars($t->text('friends_pending_with'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-empty-incoming="<?= htmlspecialchars($t->text('friends_incoming_empty'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-empty-outgoing="<?= htmlspecialchars($t->text('friends_outgoing_empty'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-empty-friends="<?= htmlspecialchars($t->text('friends_empty'), ENT_QUOTES, 'UTF-8') ?>"
  data-training-interest-action="<?= htmlspecialchars($t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>"
  data-training-interest-sent="<?= htmlspecialchars($t->text('training_interest_sent'), ENT_QUOTES, 'UTF-8') ?>"
  data-notifications-empty="<?= htmlspecialchars($t->text('notifications_empty'), ENT_QUOTES, 'UTF-8') ?>"
  data-notifications-unread-suffix="<?= htmlspecialchars($t->text('notifications_unread_count_suffix'), ENT_QUOTES, 'UTF-8') ?>"
></div>
<?php require __DIR__ . '/cookie-notice.php'; ?>
<?php require __DIR__ . '/scroll-top.php'; ?>
<script src="/js/social-interactions.js?v=20260316a" defer></script>
  </div>
</div>
