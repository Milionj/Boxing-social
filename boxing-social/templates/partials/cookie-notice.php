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
  <p class="cookie-notice__text">
    <?= htmlspecialchars($t->text('cookie_notice_text'), ENT_QUOTES, 'UTF-8') ?>
  </p>
  <div class="cookie-notice__actions">
    <a href="/cookie-preferences"><?= htmlspecialchars($t->text('cookie_notice_link'), ENT_QUOTES, 'UTF-8') ?></a>
    <button type="button" data-cookie-notice-dismiss><?= htmlspecialchars($t->text('cookie_notice_acknowledge'), ENT_QUOTES, 'UTF-8') ?></button>
  </div>
</aside>
<style>
  .cookie-notice {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 2000;
    width: min(460px, calc(100vw - 24px));
    padding: 16px 18px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 22px;
    background: rgba(17, 20, 24, 0.94);
    box-shadow: 0 20px 48px rgba(5, 7, 10, 0.28);
    box-sizing: border-box;
    backdrop-filter: blur(10px);
  }

  .cookie-notice__text {
    margin: 0;
    color: #f1e9dc;
    line-height: 1.65;
  }

  .cookie-notice__actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 14px;
  }

  .cookie-notice__actions a {
    color: #d6cab8;
    font-weight: 700;
    text-decoration: none;
  }

  .cookie-notice__actions button {
    min-height: 42px;
    padding: 0 16px;
    border: 0;
    border-radius: 999px;
    background: #9D2C20;
    color: #fff8f1;
    font: inherit;
    font-weight: 800;
    cursor: pointer;
  }

  .cookie-notice__actions button:hover {
    background: #9D2C20;
  }

  @media (max-width: 640px) {
    .cookie-notice {
      right: 12px;
      bottom: 12px;
      width: calc(100vw - 24px);
      padding: 14px;
    }

    .cookie-notice__actions {
      justify-content: stretch;
    }

    .cookie-notice__actions a,
    .cookie-notice__actions button {
      width: 100%;
      text-align: center;
    }
  }
</style>
<script>
  (function () {
    const notice = document.querySelector('[data-cookie-notice]');
    const dismissButton = document.querySelector('[data-cookie-notice-dismiss]');

    if (!notice || !dismissButton) {
      return;
    }

    dismissButton.addEventListener('click', function () {
      const secureFlag = window.location.protocol === 'https:' ? '; Secure' : '';
      document.cookie = '<?= $cookieNoticeName ?>=1; Max-Age=' + (180 * 24 * 60 * 60) + '; Path=/; SameSite=Lax' + secureFlag;
      notice.remove();
    });
  })();
</script>
