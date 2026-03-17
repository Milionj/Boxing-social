<?php
declare(strict_types=1);
?>
<aside
  id="notifications-drawer"
  class="notifications-drawer<?= $navNotificationsRequested ? ' is-open' : '' ?>"
  data-notifications-drawer
  data-notifications-scope
  data-open="<?= $navNotificationsRequested ? '1' : '0' ?>"
  data-close-url="<?= htmlspecialchars($navNotificationsCloseUrl, ENT_QUOTES, 'UTF-8') ?>"
  aria-hidden="<?= $navNotificationsRequested ? 'false' : 'true' ?>"
>
  <div class="notifications-drawer__header">
    <div>
      <p class="notifications-drawer__eyebrow"><?= htmlspecialchars($t->text('notifications_drawer_label'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2><?= htmlspecialchars($t->text('nav_notifications'), ENT_QUOTES, 'UTF-8') ?></h2>
    </div>

    <button class="notifications-drawer__close" type="button" aria-label="<?= htmlspecialchars($t->text('common_close'), ENT_QUOTES, 'UTF-8') ?>" data-notifications-close>
      ×
    </button>
  </div>

  <div class="notifications-drawer__toolbar">
    <span class="notifications-drawer__badge" data-notifications-badge data-count-format="suffix"><?= $navUnreadNotificationsTotal ?> <?= htmlspecialchars($t->text('notifications_unread_count_suffix'), ENT_QUOTES, 'UTF-8') ?></span>

    <form method="post" action="/notifications/read-all" data-notifications-mark-all-form>
      <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($navNotificationsPanelUrl, ENT_QUOTES, 'UTF-8') ?>">
      <button class="notifications-drawer__mark-all" type="submit">
        <?= htmlspecialchars($t->text('notifications_mark_all'), ENT_QUOTES, 'UTF-8') ?>
      </button>
    </form>
  </div>

  <p class="interaction-feedback is-error" data-interaction-feedback hidden></p>

  <?php if ($navNotificationItems === []): ?>
    <div class="notifications-drawer__empty" data-notifications-empty>
      <p><?= htmlspecialchars($t->text('notifications_empty'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php else: ?>
    <div class="notifications-drawer__list" data-notifications-list>
      <?php foreach ($navNotificationItems as $notification): ?>
        <article
          class="notifications-drawer__item<?= ((int) $notification['is_read'] === 0) ? ' is-unread' : '' ?>"
          data-notification-item
          data-notification-id="<?= (int) $notification['id'] ?>"
          data-notification-open-url="<?= htmlspecialchars((string) ($notification['open_url'] ?? $notification['target_url'] ?? '/notifications'), ENT_QUOTES, 'UTF-8') ?>"
        >
          <div class="notifications-drawer__item-head">
            <strong><?= htmlspecialchars((string) ($notification['display_type'] ?? $notification['type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            <time><?= htmlspecialchars((string) $notification['created_at'], ENT_QUOTES, 'UTF-8') ?></time>
          </div>

          <p class="notifications-drawer__item-content">
            <?= htmlspecialchars((string) ($notification['display_content'] ?? $notification['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
          </p>

          <div class="notifications-drawer__item-foot">
            <p class="notifications-drawer__actor">
              <?php if (!empty($notification['actor_username'])): ?>
                <span><?= htmlspecialchars($t->text('notifications_actor'), ENT_QUOTES, 'UTF-8') ?></span>
                <a href="/user?username=<?= rawurlencode((string) $notification['actor_username']) ?>">
                  <?= htmlspecialchars((string) $notification['actor_username'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              <?php else: ?>
                <span><?= htmlspecialchars($t->text('notifications_actor'), ENT_QUOTES, 'UTF-8') ?></span>
                <?= htmlspecialchars($t->text('notifications_system'), ENT_QUOTES, 'UTF-8') ?>
              <?php endif; ?>
            </p>

            <div class="notifications-drawer__actions">
              <a class="notifications-drawer__open" href="<?= htmlspecialchars((string) ($notification['open_url'] ?? $notification['target_url'] ?? '/notifications'), ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($t->text('notifications_open'), ENT_QUOTES, 'UTF-8') ?>
              </a>

              <?php if ((int) $notification['is_read'] === 0): ?>
                <form method="post" action="/notifications/read" data-notification-read-form>
                  <input type="hidden" name="notification_id" value="<?= (int) $notification['id'] ?>">
                  <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($navNotificationsPanelUrl, ENT_QUOTES, 'UTF-8') ?>">
                  <button class="notifications-drawer__read" type="submit">
                    <?= htmlspecialchars($t->text('notifications_mark_read'), ENT_QUOTES, 'UTF-8') ?>
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</aside>
<script>
  (function () {
    const toggle = document.querySelector('[data-notifications-toggle]');
    const drawer = document.querySelector('[data-notifications-drawer]');

    if (!toggle || !drawer) {
      return;
    }

    const closeButton = drawer.querySelector('[data-notifications-close]');
    const closeUrl = drawer.dataset.closeUrl || '/';

    const syncState = function (isOpen, syncUrl) {
      drawer.classList.toggle('is-open', isOpen);
      drawer.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      toggle.classList.toggle('is-open', isOpen);
      document.body.classList.toggle('notifications-drawer-open', isOpen);

      if (syncUrl) {
        history.replaceState(null, '', isOpen ? toggle.getAttribute('href') : closeUrl);
      }
    };

    if (drawer.dataset.open === '1') {
      syncState(true, false);
    }

    toggle.addEventListener('click', function (event) {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
        return;
      }

      event.preventDefault();
      syncState(!drawer.classList.contains('is-open'), true);
    });

    if (closeButton) {
      closeButton.addEventListener('click', function () {
        syncState(false, true);
      });
    }

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
        syncState(false, true);
      }
    });

    document.addEventListener('click', function (event) {
      if (!drawer.classList.contains('is-open')) {
        return;
      }

      const target = event.target;
      if (!(target instanceof Node)) {
        return;
      }

      if (drawer.contains(target) || toggle.contains(target)) {
        return;
      }

      syncState(false, true);
    });
  })();
</script>
