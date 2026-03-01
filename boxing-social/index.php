<?php
declare(strict_types=1);

// Fallback entrypoint when web root points to project root instead of /public.
$target = '/public/';
header('Location: ' . $target, true, 302);
exit;
