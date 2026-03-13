<?php
declare(strict_types=1);

if (!isset($t) || !$t instanceof \App\Services\Translator) {
    require __DIR__ . '/app-locale.php';
}

$navUser = $_SESSION['user']['username'] ?? null;
$navRole = $_SESSION['user']['role'] ?? null;
$navUnreadNotifications = 0;
$navNotificationsEnabled = true;
$navTheme = 'systeme';

if (is_int($_SESSION['user']['id'] ?? null)) {
    $navUserId = (int) $_SESSION['user']['id'];
    $navSettings = new \App\Models\UserSettings();

    // La navbar centralise la lecture des preferences globales de l'utilisateur.
    // Comme elle est incluse sur presque toutes les pages "app-shell",
    // c'est le bon point d'entrée pour appliquer le theme et le badge notif.
    $navNotificationsEnabled = $navSettings->notificationsEnabledForUser($navUserId);
    $navTheme = $navSettings->themeForUser($navUserId);

    if ($navNotificationsEnabled) {
        $navUnreadNotifications = (new \App\Models\Notification())->unreadCount($navUserId);
    }
}
?>
<script>
  // On applique le theme au <body> partage par toutes les pages qui chargent cette navbar.
  // "systeme" laisse le CSS de base, "clair" et "sombre" forcent une variation visible.
  document.body.dataset.theme = <?= json_encode($navTheme, JSON_UNESCAPED_SLASHES) ?>;
</script>
<nav class="app-nav">
  <div class="app-nav__brand">
    <a href="/">BOXING SOCIAL</a>
  </div>

  <div class="app-nav__links">
    <a href="/profile"><?= htmlspecialchars($t->text('nav_profile'), ENT_QUOTES, 'UTF-8') ?></a>
    <a href="/posts/create"><?= htmlspecialchars($t->text('nav_publish'), ENT_QUOTES, 'UTF-8') ?></a>
    <a href="/friends"><?= htmlspecialchars($t->text('nav_friends'), ENT_QUOTES, 'UTF-8') ?></a>
    <a href="/messages"><?= htmlspecialchars($t->text('nav_messages'), ENT_QUOTES, 'UTF-8') ?></a>
    <a href="/notifications" class="app-nav__notif">
      <?= htmlspecialchars($t->text('nav_notifications'), ENT_QUOTES, 'UTF-8') ?>
      <?php if ($navNotificationsEnabled && $navUnreadNotifications > 0): ?>
        <span class="app-nav__badge"><?= $navUnreadNotifications ?></span>
      <?php endif; ?>
    </a>
    <a href="/search"><?= htmlspecialchars($t->text('nav_search'), ENT_QUOTES, 'UTF-8') ?></a>
    <?php if ($navRole === 'admin'): ?>
      <a href="/admin"><?= htmlspecialchars($t->text('nav_admin'), ENT_QUOTES, 'UTF-8') ?></a>
    <?php endif; ?>
    <details class="app-nav__menu">
      <summary><?= htmlspecialchars($t->text('nav_more'), ENT_QUOTES, 'UTF-8') ?></summary>
      <div class="app-nav__dropdown">
        <a href="/contact"><?= htmlspecialchars($t->text('nav_contact'), ENT_QUOTES, 'UTF-8') ?></a>
        <a href="/privacy"><?= htmlspecialchars($t->text('nav_privacy'), ENT_QUOTES, 'UTF-8') ?></a>
        <a href="/settings"><?= htmlspecialchars($t->text('nav_settings'), ENT_QUOTES, 'UTF-8') ?></a>
      </div>
    </details>
  </div>

  <div class="app-nav__user">
    <?php if ($navUser !== null): ?>
      <span><?= htmlspecialchars((string) $navUser, ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>
    <form method="post" action="/logout" class="app-nav__logout">
      <button type="submit"><?= htmlspecialchars($t->text('nav_logout'), ENT_QUOTES, 'UTF-8') ?></button>
    </form>
  </div>
</nav>
