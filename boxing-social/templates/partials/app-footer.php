<?php
declare(strict_types=1);

if (!isset($t) || !$t instanceof \App\Services\Translator) {
    require __DIR__ . '/app-locale.php';
}
?>
<footer class="app-footer">
  <div class="app-footer__brand">
    <a href="/">BOXING SOCIAL</a>
  </div>

  <div class="app-footer__meta">
    <p>&copy; <?= htmlspecialchars($t->text('footer_rights'), ENT_QUOTES, 'UTF-8') ?></p>
  </div>

  <div class="app-footer__links">
    <a href="/contact"><?= htmlspecialchars($t->text('nav_contact'), ENT_QUOTES, 'UTF-8') ?></a>
    <a href="/privacy"><?= htmlspecialchars($t->text('privacy_title'), ENT_QUOTES, 'UTF-8') ?></a>
    <a href="/settings"><?= htmlspecialchars($t->text('nav_settings'), ENT_QUOTES, 'UTF-8') ?></a>
  </div>
</footer>
