<?php
declare(strict_types=1);

if (!isset($t) || !$t instanceof \App\Services\Translator) {
    require __DIR__ . '/app-locale.php';
}
?>
<div
  hidden
  data-social-i18n
  data-csrf-token="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
  data-error-generic="<?= htmlspecialchars($t->text('interaction_generic_error'), ENT_QUOTES, 'UTF-8') ?>"
  data-friend-request-sent="<?= htmlspecialchars($t->text('friends_request_sent'), ENT_QUOTES, 'UTF-8') ?>"
  data-friend-request-cancelled="<?= htmlspecialchars($t->text('friends_request_cancelled'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-open-profile="<?= htmlspecialchars($t->text('friends_open_profile'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-pending-with="<?= htmlspecialchars($t->text('friends_pending_with'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-cancel="<?= htmlspecialchars($t->text('friends_cancel'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-remove="<?= htmlspecialchars($t->text('friends_remove'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-removed="<?= htmlspecialchars($t->text('friends_removed'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-empty-incoming="<?= htmlspecialchars($t->text('friends_incoming_empty'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-empty-outgoing="<?= htmlspecialchars($t->text('friends_outgoing_empty'), ENT_QUOTES, 'UTF-8') ?>"
  data-friends-empty-friends="<?= htmlspecialchars($t->text('friends_empty'), ENT_QUOTES, 'UTF-8') ?>"
  data-training-interest-action="<?= htmlspecialchars($t->text('training_interest_action'), ENT_QUOTES, 'UTF-8') ?>"
  data-training-interest-sent="<?= htmlspecialchars($t->text('training_interest_sent'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-title="<?= htmlspecialchars($t->text('sports_preview_title'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-intro="<?= htmlspecialchars($t->text('sports_preview_intro'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-season="<?= htmlspecialchars($t->text('sports_preview_season'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-refresh="<?= htmlspecialchars($t->text('sports_preview_refresh'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-loading="<?= htmlspecialchars($t->text('sports_preview_loading'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-empty="<?= htmlspecialchars($t->text('sports_preview_empty'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-unavailable="<?= htmlspecialchars($t->text('sports_preview_unavailable'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-not-configured="<?= htmlspecialchars($t->text('sports_preview_not_configured'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-select-event="<?= htmlspecialchars($t->text('sports_preview_select_event'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-status="<?= htmlspecialchars($t->text('sports_preview_status'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-venue="<?= htmlspecialchars($t->text('sports_preview_venue'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-promotion="<?= htmlspecialchars($t->text('sports_preview_promotion'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-details="<?= htmlspecialchars($t->text('sports_preview_details'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-fights="<?= htmlspecialchars($t->text('sports_preview_fights'), ENT_QUOTES, 'UTF-8') ?>"
  data-sports-preview-updated="<?= htmlspecialchars($t->text('sports_preview_updated'), ENT_QUOTES, 'UTF-8') ?>"
  data-feed-side-collapse="<?= htmlspecialchars($t->text('feed_side_collapse'), ENT_QUOTES, 'UTF-8') ?>"
  data-feed-side-expand="<?= htmlspecialchars($t->text('feed_side_expand'), ENT_QUOTES, 'UTF-8') ?>"
  data-notifications-empty="<?= htmlspecialchars($t->text('notifications_empty'), ENT_QUOTES, 'UTF-8') ?>"
  data-notifications-unread-suffix="<?= htmlspecialchars($t->text('notifications_unread_count_suffix'), ENT_QUOTES, 'UTF-8') ?>"
></div>
<?php require __DIR__ . '/cookie-notice.php'; ?>
<?php require __DIR__ . '/scroll-top.php'; ?>
<script src="/js/social-interactions.js?v=20260317c" defer></script>
  </div>
</div>
