<?php
declare(strict_types=1);

$scrollTopPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
?>
<?php if ($scrollTopPath !== '/messages'): ?>
  <a class="scroll-top" href="#" aria-label="Retour en haut" title="Retour en haut" data-scroll-top>↑</a>
<?php endif; ?>
<script src="/js/vendor/dompurify.min.js?v=20260317a" defer></script>
<script src="/js/app-security.js?v=20260320a" defer></script>
