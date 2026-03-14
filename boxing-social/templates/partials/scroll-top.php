<?php
declare(strict_types=1);

$scrollTopPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
if ($scrollTopPath === '/messages') {
    return;
}
?>
<a class="scroll-top" href="#" aria-label="Retour en haut" title="Retour en haut" data-scroll-top>↑</a>
<script>
  (function () {
    const button = document.querySelector('[data-scroll-top]');
    if (!button) {
      return;
    }

    const threshold = 260;

    const syncVisibility = function () {
      button.classList.toggle('is-visible', window.scrollY > threshold);
    };

    syncVisibility();
    window.addEventListener('scroll', syncVisibility, { passive: true });
  })();
</script>
