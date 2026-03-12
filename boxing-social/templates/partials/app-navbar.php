<?php
declare(strict_types=1);

$navUser = $_SESSION['user']['username'] ?? null;
$navRole = $_SESSION['user']['role'] ?? null;
$navUnreadNotifications = 0;

if (is_int($_SESSION['user']['id'] ?? null)) {
    $navUnreadNotifications = (new \App\Models\Notification())->unreadCount((int) $_SESSION['user']['id']);
}
?>
<nav class="app-nav">
  <div class="app-nav__brand">
    <a href="/">BOXING SOCIAL</a>
  </div>

  <div class="app-nav__links">
    <a href="/profile">Profil</a>
    <a href="/posts/create">Publier</a>
    <a href="/friends">Amis</a>
    <a href="/messages">Messages</a>
    <a href="/notifications" class="app-nav__notif">
      Notifications
      <?php if ($navUnreadNotifications > 0): ?>
        <span class="app-nav__badge"><?= $navUnreadNotifications ?></span>
      <?php endif; ?>
    </a>
    <a href="/search">Recherche</a>
    <?php if ($navRole === 'admin'): ?>
      <a href="/admin">Administration</a>
    <?php endif; ?>
  </div>

  <div class="app-nav__user">
    <?php if ($navUser !== null): ?>
      <span><?= htmlspecialchars((string) $navUser, ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>
    <form method="post" action="/logout" class="app-nav__logout">
      <button type="submit">Se deconnecter</button>
    </form>
  </div>
</nav>
