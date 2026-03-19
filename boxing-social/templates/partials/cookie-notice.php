<?php
declare(strict_types=1);

if (!isset($t) || !$t instanceof \App\Services\Translator) {
    require __DIR__ . '/app-locale.php';
}

// Cette banniere reste purement informative :
// elle sert a expliquer l'usage du cookie de session necessaire,
// puis a memoriser le fait que l'utilisateur a compris l'information.
$cookieNoticeName = 'boxing_social_cookie_notice_dismissed';

if (($_COOKIE[$cookieNoticeName] ?? '') === '1') {
    return;
}
?>
<aside class="cookie-notice" data-cookie-notice>
  <input type="hidden" value="<?= htmlspecialchars($cookieNoticeName, ENT_QUOTES, 'UTF-8') ?>" data-cookie-notice-name>
  <p class="cookie-notice__text">
    <?= htmlspecialchars($t->text('cookie_notice_text'), ENT_QUOTES, 'UTF-8') ?>
  </p>
  <div class="cookie-notice__actions">
    <a href="/cookie-preferences"><?= htmlspecialchars($t->text('cookie_notice_link'), ENT_QUOTES, 'UTF-8') ?></a>
    <button type="button" data-cookie-notice-dismiss><?= htmlspecialchars($t->text('cookie_notice_acknowledge'), ENT_QUOTES, 'UTF-8') ?></button>
  </div>
</aside>
