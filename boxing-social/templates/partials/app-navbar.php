<?php
declare(strict_types=1);

if (!isset($t) || !$t instanceof \App\Services\Translator) {
    require __DIR__ . '/app-locale.php';
}

$navUser = $_SESSION['user']['username'] ?? null;
$navRole = $_SESSION['user']['role'] ?? null;
$navUnreadNotifications = 0;
$navUnreadNotificationsTotal = 0;
$navNotificationsEnabled = true;
$navTheme = 'systeme';
$navNotificationItems = [];

if (is_int($_SESSION['user']['id'] ?? null)) {
    $navUserId = (int) $_SESSION['user']['id'];
    $navSettings = new \App\Models\UserSettings();
    $navNotificationModel = new \App\Models\Notification();

    // Ce partial pilote maintenant tout le shell applicatif.
    // Il lit les preferences utilisateur une seule fois puis construit
    // la colonne gauche et la topbar sticky partagees par les pages.
    $navNotificationsEnabled = $navSettings->notificationsEnabledForUser($navUserId);
    $navTheme = $navSettings->themeForUser($navUserId);
    $navNotificationItems = $navNotificationModel->latestForUser($navUserId, 12);
    $navUnreadNotificationsTotal = $navNotificationModel->unreadCount($navUserId);

    if ($navNotificationsEnabled) {
        $navUnreadNotifications = $navUnreadNotificationsTotal;
    }
}

$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$currentQuery = trim((string) ($_GET['q'] ?? ''));
$isHomePage = ($currentPath === '/');
$currentRequestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

$matchesPath = static function (string $path, string $currentPath): bool {
    if ($path === '/') {
        return $currentPath === '/';
    }

    return $currentPath === $path || str_starts_with($currentPath, $path . '/');
};

$resolveNotificationUrl = static function (array $notification): string {
    $type = (string) ($notification['type'] ?? '');
    $entityId = (int) ($notification['entity_id'] ?? 0);
    $actorUsername = (string) ($notification['actor_username'] ?? '');

    if (($type === 'like' || $type === 'comment') && $entityId > 0) {
        return '/post?id=' . $entityId;
    }

    if ($type === 'message') {
        return $actorUsername !== '' ? '/messages?username=' . rawurlencode($actorUsername) : '/messages';
    }

    if ($type === 'friend_request' || $type === 'friend_accept') {
        return $actorUsername !== '' ? '/user?username=' . rawurlencode($actorUsername) : '/friends';
    }

    return '/notifications';
};

foreach ($navNotificationItems as &$navNotificationItem) {
    $navNotificationItem['target_url'] = $resolveNotificationUrl($navNotificationItem);
}
unset($navNotificationItem);

$buildNotificationsPanelUrl = static function (string $requestUri, bool $open): string {
    $path = (string) (parse_url($requestUri, PHP_URL_PATH) ?? '/');
    $queryString = (string) (parse_url($requestUri, PHP_URL_QUERY) ?? '');
    parse_str($queryString, $params);

    if ($open) {
        $params['notifications'] = '1';
    } else {
        unset($params['notifications']);
    }

    $query = http_build_query($params);

    return $path . ($query !== '' ? '?' . $query : '');
};

$navNotificationsRequested = ((string) ($_GET['notifications'] ?? '') === '1') || $matchesPath('/notifications', $currentPath);
$navNotificationsPanelUrl = $buildNotificationsPanelUrl($currentRequestUri, true);
$navNotificationsCloseUrl = $buildNotificationsPanelUrl($currentRequestUri, false);

$topbarTitle = match (true) {
    $currentPath === '/' => $t->text('home_title'),
    $matchesPath('/profile', $currentPath) => $t->text('profile_title'),
    $matchesPath('/posts', $currentPath) => $t->text('posts_title'),
    $matchesPath('/friends', $currentPath) => $t->text('friends_title'),
    $matchesPath('/messages', $currentPath) => $t->text('nav_messages'),
    $matchesPath('/notifications', $currentPath) => $t->text('nav_notifications'),
    $matchesPath('/search', $currentPath) => $t->text('nav_search'),
    $matchesPath('/admin', $currentPath) => $t->text('admin_title'),
    $matchesPath('/contact', $currentPath) => $t->text('contact_title'),
    $matchesPath('/privacy', $currentPath) => $t->text('privacy_title'),
    $matchesPath('/settings', $currentPath) => $t->text('settings_title'),
    default => 'Boxing Social',
};
?>
<script>
  document.body.dataset.theme = <?= json_encode($navTheme, JSON_UNESCAPED_SLASHES) ?>;
</script>
<div class="app-shell-layout">
  <aside class="app-sidebar">
    <div class="app-sidebar__brand">
      <a href="/">
        <span class="app-nav__brand-mark">B</span>
        <span class="app-nav__brand-copy">
          <strong>Boxing Social</strong>
          <small>Communauté boxe</small>
        </span>
      </a>
    </div>

    <a class="app-sidebar__compose" href="/posts/create"><?= htmlspecialchars($t->text('nav_publish'), ENT_QUOTES, 'UTF-8') ?></a>

    <nav class="app-sidebar__nav">
      <a class="app-sidebar__link<?= $matchesPath('/', $currentPath) ? ' is-active' : '' ?>" href="/">
        <span><?= htmlspecialchars($t->text('home_title'), ENT_QUOTES, 'UTF-8') ?></span>
      </a>
      <a class="app-sidebar__link<?= $matchesPath('/profile', $currentPath) ? ' is-active' : '' ?>" href="/profile">
        <span><?= htmlspecialchars($t->text('nav_profile'), ENT_QUOTES, 'UTF-8') ?></span>
      </a>
      <a class="app-sidebar__link<?= $matchesPath('/posts', $currentPath) ? ' is-active' : '' ?>" href="/posts">
        <span><?= htmlspecialchars($t->text('posts_title'), ENT_QUOTES, 'UTF-8') ?></span>
      </a>
      <a class="app-sidebar__link<?= $matchesPath('/friends', $currentPath) ? ' is-active' : '' ?>" href="/friends">
        <span><?= htmlspecialchars($t->text('nav_friends'), ENT_QUOTES, 'UTF-8') ?></span>
      </a>
      <a class="app-sidebar__link<?= $matchesPath('/messages', $currentPath) ? ' is-active' : '' ?>" href="/messages">
        <span><?= htmlspecialchars($t->text('nav_messages'), ENT_QUOTES, 'UTF-8') ?></span>
      </a>
      <a
        class="app-sidebar__link<?= ($matchesPath('/notifications', $currentPath) || $navNotificationsRequested) ? ' is-active' : '' ?>"
        href="<?= htmlspecialchars($navNotificationsPanelUrl, ENT_QUOTES, 'UTF-8') ?>"
        data-notifications-toggle
        aria-expanded="<?= $navNotificationsRequested ? 'true' : 'false' ?>"
        aria-controls="notifications-drawer"
      >
        <span><?= htmlspecialchars($t->text('nav_notifications'), ENT_QUOTES, 'UTF-8') ?></span>
        <?php if ($navNotificationsEnabled && $navUnreadNotifications > 0): ?>
          <span class="app-sidebar__count" data-notifications-nav-count><?= $navUnreadNotifications ?></span>
        <?php endif; ?>
      </a>
      <a class="app-sidebar__link<?= $matchesPath('/search', $currentPath) ? ' is-active' : '' ?>" href="/search">
        <span><?= htmlspecialchars($t->text('nav_search'), ENT_QUOTES, 'UTF-8') ?></span>
      </a>
      <?php if ($navRole === 'admin'): ?>
        <a class="app-sidebar__link<?= $matchesPath('/admin', $currentPath) ? ' is-active' : '' ?>" href="/admin">
          <span><?= htmlspecialchars($t->text('nav_admin'), ENT_QUOTES, 'UTF-8') ?></span>
        </a>
      <?php endif; ?>

      <details class="app-sidebar__group">
        <summary class="app-sidebar__link<?= ($matchesPath('/contact', $currentPath) || $matchesPath('/privacy', $currentPath) || $matchesPath('/settings', $currentPath)) ? ' is-active' : '' ?>">
          <span><?= htmlspecialchars($t->text('nav_more'), ENT_QUOTES, 'UTF-8') ?></span>
        </summary>
        <div class="app-sidebar__subnav">
          <a href="/contact"><?= htmlspecialchars($t->text('nav_contact'), ENT_QUOTES, 'UTF-8') ?></a>
          <a href="/privacy"><?= htmlspecialchars($t->text('nav_privacy'), ENT_QUOTES, 'UTF-8') ?></a>
          <a href="/settings"><?= htmlspecialchars($t->text('nav_settings'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
      </details>
    </nav>
  </aside>

  <div class="app-shell-stage">
    <header class="app-topbar<?= $isHomePage ? '' : ' app-topbar--compact' ?>">
      <?php if ($isHomePage): ?>
        <form class="app-topbar__search" method="get" action="/search" autocomplete="off">
          <input type="text" name="q" value="<?= htmlspecialchars($currentQuery, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t->text('home_search_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit"><?= htmlspecialchars($t->text('home_search_button'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
      <?php else: ?>
        <div class="app-topbar__context">
          <p class="app-topbar__eyebrow">Boxing Social</p>
          <h1 class="app-topbar__title"><?= htmlspecialchars($topbarTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
      <?php endif; ?>

      <div class="app-topbar__actions">
        <?php if ($navUser !== null): ?>
          <a class="app-topbar__user" href="/profile"><?= htmlspecialchars((string) $navUser, ENT_QUOTES, 'UTF-8') ?></a>
          <form method="post" action="/logout" class="app-topbar__logout">
            <button type="submit"><?= htmlspecialchars($t->text('nav_logout'), ENT_QUOTES, 'UTF-8') ?></button>
          </form>
        <?php else: ?>
          <a class="app-topbar__login" href="/login"><?= $htmlLang === 'en' ? 'Login' : 'Connexion' ?></a>
        <?php endif; ?>
      </div>
    </header>
    <?php if ($navUser !== null): ?>
      <?php require __DIR__ . '/notifications-drawer.php'; ?>
    <?php endif; ?>
