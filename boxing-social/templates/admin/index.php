<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<?php
$translateOrFallback = static function (string $key, string $fallback) use ($t): string {
    $translated = $t->text($key);
    return $translated !== $key ? $translated : $fallback;
};

$truncate = static function (?string $text, int $limit = 120): string {
    $value = trim((string) $text);
    if ($value === '') {
        return '—';
    }

    $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    if ($length <= $limit) {
        return $value;
    }

    $slice = function_exists('mb_substr') ? mb_substr($value, 0, $limit - 1) : substr($value, 0, $limit - 1);
    return rtrim($slice) . '…';
};

$buildAdminUrl = static function (array $overrides = []): string {
    $params = $_GET;

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
            continue;
        }

        $params[$key] = (string) $value;
    }

    $query = http_build_query($params);

    return '/admin' . ($query !== '' ? '?' . $query : '');
};

$renderHiddenInputs = static function (array $exclude = []): string {
    $excludeLookup = array_fill_keys($exclude, true);
    $html = '';

    foreach ($_GET as $key => $value) {
        if (isset($excludeLookup[$key]) || is_array($value)) {
            continue;
        }

        $html .= '<input type="hidden" name="' . htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '">';
    }

    return $html;
};

$renderPagination = static function (array $pagination, string $pageParam) use ($buildAdminUrl, $t): string {
    if (($pagination['total_pages'] ?? 1) <= 1) {
        return '';
    }

    $currentPage = (int) $pagination['current_page'];
    $totalPages = (int) $pagination['total_pages'];

    ob_start();
    ?>
    <nav class="admin-pagination" aria-label="<?= htmlspecialchars($t->text('pagination_label'), ENT_QUOTES, 'UTF-8') ?>">
      <?php if ($currentPage > 1): ?>
        <a class="admin-pagination__link" href="<?= htmlspecialchars($buildAdminUrl([$pageParam => $currentPage - 1]), ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($t->text('pagination_previous'), ENT_QUOTES, 'UTF-8') ?>
        </a>
      <?php endif; ?>

      <span class="admin-pagination__status">
        <?= htmlspecialchars($t->text('pagination_label'), ENT_QUOTES, 'UTF-8') ?>
        <?= $currentPage ?> / <?= $totalPages ?>
      </span>

      <?php if ($currentPage < $totalPages): ?>
        <a class="admin-pagination__link" href="<?= htmlspecialchars($buildAdminUrl([$pageParam => $currentPage + 1]), ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($t->text('pagination_next'), ENT_QUOTES, 'UTF-8') ?>
        </a>
      <?php endif; ?>
    </nav>
    <?php

    return (string) ob_get_clean();
};

$formatLogAction = static function (array $log) use ($translateOrFallback): string {
    $action = (string) ($log['action'] ?? '');
    return $translateOrFallback('admin_log_action_' . $action, $action);
};

$formatLogTarget = static function (array $log) use ($translateOrFallback): string {
    $targetType = (string) ($log['target_type'] ?? '');
    $targetId = (int) ($log['target_id'] ?? 0);
    $label = $translateOrFallback('admin_log_target_' . $targetType, $targetType);

    return $targetId > 0 ? $label . ' #' . $targetId : $label;
};

$formatLogDetails = static function (array $log) use ($t, $truncate): array {
    $details = $log['details_map'] ?? null;
    if (!is_array($details) || $details === []) {
        return [];
    }

    $lines = [];

    if (($details['username'] ?? '') !== '') {
        $lines[] = [
            'label' => $t->text('profile_username'),
            'value' => (string) $details['username'],
        ];
    }

    if (($details['email'] ?? '') !== '') {
        $lines[] = [
            'label' => $t->text('admin_email'),
            'value' => (string) $details['email'],
        ];
    }

    if (array_key_exists('is_active', $details)) {
        $lines[] = [
            'label' => $t->text('admin_status'),
            'value' => ((bool) $details['is_active']) ? $t->text('admin_active') : $t->text('admin_disabled'),
        ];
    }

    if (($details['title'] ?? '') !== '') {
        $lines[] = [
            'label' => $t->text('admin_title_column'),
            'value' => $truncate((string) $details['title'], 90),
        ];
    }

    if (($details['visibility'] ?? '') !== '') {
        $lines[] = [
            'label' => $t->text('admin_visibility'),
            'value' => (string) $details['visibility'],
        ];
    }

    if (($details['post_type'] ?? '') !== '') {
        $postType = (string) $details['post_type'];
        $lines[] = [
            'label' => $t->text('admin_post_type'),
            'value' => $postType === 'entrainement' ? $t->text('posts_type_training') : $t->text('posts_type_publication'),
        ];
    }

    if (($details['post_id'] ?? 0) > 0) {
        $lines[] = [
            'label' => $t->text('admin_post'),
            'value' => '#' . (int) $details['post_id'],
        ];
    }

    if (($details['content'] ?? '') !== '') {
        $lines[] = [
            'label' => $t->text('admin_content'),
            'value' => $truncate((string) $details['content'], 90),
        ];
    }

    return $lines;
};

$logActionOptions = [
    '' => $t->text('search_scope_all'),
    'user_activated' => $t->text('admin_log_action_user_activated'),
    'user_disabled' => $t->text('admin_log_action_user_disabled'),
    'post_deleted' => $t->text('admin_log_action_post_deleted'),
    'comment_deleted' => $t->text('admin_log_action_comment_deleted'),
];

$logTargetOptions = [
    '' => $t->text('search_scope_all'),
    'user' => $t->text('admin_log_target_user'),
    'post' => $t->text('admin_log_target_post'),
    'comment' => $t->text('admin_log_target_comment'),
];
?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('admin_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260317p">
  <link rel="stylesheet" href="/css/admin-index.css?v=20260317a">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="admin-page app-main">
    <section class="admin-hero">
      <h1><?= htmlspecialchars($t->text('admin_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('admin_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <section class="admin-stats">
      <article class="admin-stat-card">
        <span><?= htmlspecialchars($t->text('admin_stats_users'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong><?= (int) $stats['users'] ?></strong>
      </article>
      <article class="admin-stat-card">
        <span><?= htmlspecialchars($t->text('admin_stats_posts'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong><?= (int) $stats['posts'] ?></strong>
      </article>
      <article class="admin-stat-card">
        <span><?= htmlspecialchars($t->text('admin_stats_comments'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong><?= (int) $stats['comments'] ?></strong>
      </article>
      <article class="admin-stat-card">
        <span><?= htmlspecialchars($t->text('admin_stats_logs'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong><?= (int) $stats['logs'] ?></strong>
      </article>
    </section>

    <section class="admin-card">
      <div class="admin-card__head">
        <div>
          <p class="admin-card__eyebrow"><?= htmlspecialchars($t->text('admin_filters'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($t->text('admin_users'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <p class="admin-card__summary"><?= (int) $usersPagination['total'] ?> <?= htmlspecialchars($t->text('admin_results_summary'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <form class="admin-filters" method="get" action="/admin">
        <?= $renderHiddenInputs(['users_q', 'users_role', 'users_status', 'users_page']) ?>
        <label>
          <span>ID / <?= htmlspecialchars($t->text('profile_username'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($t->text('admin_email'), ENT_QUOTES, 'UTF-8') ?></span>
          <input type="text" name="users_q" value="<?= htmlspecialchars((string) $usersFilters['query'], ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
          <span><?= htmlspecialchars($t->text('admin_role'), ENT_QUOTES, 'UTF-8') ?></span>
          <select name="users_role">
            <option value=""><?= htmlspecialchars($t->text('search_scope_all'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="user" <?= $usersFilters['role'] === 'user' ? 'selected' : '' ?>><?= htmlspecialchars($t->text('admin_role_user'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="admin" <?= $usersFilters['role'] === 'admin' ? 'selected' : '' ?>><?= htmlspecialchars($t->text('admin_role_admin'), ENT_QUOTES, 'UTF-8') ?></option>
          </select>
        </label>
        <label>
          <span><?= htmlspecialchars($t->text('admin_status'), ENT_QUOTES, 'UTF-8') ?></span>
          <select name="users_status">
            <option value=""><?= htmlspecialchars($t->text('search_scope_all'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="active" <?= $usersFilters['status'] === 'active' ? 'selected' : '' ?>><?= htmlspecialchars($t->text('admin_active'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="disabled" <?= $usersFilters['status'] === 'disabled' ? 'selected' : '' ?>><?= htmlspecialchars($t->text('admin_disabled'), ENT_QUOTES, 'UTF-8') ?></option>
          </select>
        </label>
        <div class="admin-filters__actions">
          <button type="submit"><?= htmlspecialchars($t->text('admin_apply_filters'), ENT_QUOTES, 'UTF-8') ?></button>
          <a href="<?= htmlspecialchars($buildAdminUrl(['users_q' => null, 'users_role' => null, 'users_status' => null, 'users_page' => null]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('admin_reset_filters'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
      </form>

      <?php if ($users === []): ?>
        <p class="admin-empty"><?= htmlspecialchars($t->text('admin_no_users_match'), ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th><?= htmlspecialchars($t->text('profile_username'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_email'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_role'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_status'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_details'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_action'), ENT_QUOTES, 'UTF-8') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= (int) $u['id'] ?></td>
                  <td>
                    <a href="/user?username=<?= rawurlencode((string) $u['username']) ?>">
                      <?= htmlspecialchars((string) $u['username'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </td>
                  <td><?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $u['role'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= ((int) $u['is_active'] === 1) ? htmlspecialchars($t->text('admin_active'), ENT_QUOTES, 'UTF-8') : htmlspecialchars($t->text('admin_disabled'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <div class="admin-meta">
                      <span><?= htmlspecialchars($t->text('admin_created_at'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $u['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                      <span><?= htmlspecialchars($truncate((string) ($u['bio'] ?? ''), 90), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                  </td>
                  <td>
                    <div class="admin-actions">
                      <a class="admin-link-chip" href="/user?username=<?= rawurlencode((string) $u['username']) ?>"><?= htmlspecialchars($t->text('admin_open_profile'), ENT_QUOTES, 'UTF-8') ?></a>
                      <?php if ((int) $u['id'] === (int) $adminId): ?>
                        <span class="admin-tag admin-tag--muted"><?= htmlspecialchars($t->text('admin_current_account'), ENT_QUOTES, 'UTF-8') ?></span>
                      <?php else: ?>
                        <form method="post" action="/admin/users/toggle">
                          <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentRequestUri, ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                          <input type="hidden" name="is_active" value="<?= ((int) $u['is_active'] === 1) ? '0' : '1' ?>">
                          <button type="submit"><?= ((int) $u['is_active'] === 1) ? htmlspecialchars($t->text('admin_disable'), ENT_QUOTES, 'UTF-8') : htmlspecialchars($t->text('admin_enable'), ENT_QUOTES, 'UTF-8') ?></button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?= $renderPagination($usersPagination, 'users_page') ?>
      <?php endif; ?>
    </section>

    <section class="admin-card">
      <div class="admin-card__head">
        <div>
          <p class="admin-card__eyebrow"><?= htmlspecialchars($t->text('admin_filters'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($t->text('admin_posts'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <p class="admin-card__summary"><?= (int) $postsPagination['total'] ?> <?= htmlspecialchars($t->text('admin_results_summary'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <form class="admin-filters" method="get" action="/admin">
        <?= $renderHiddenInputs(['posts_q', 'posts_visibility', 'posts_type', 'posts_page']) ?>
        <label>
          <span>ID / <?= htmlspecialchars($t->text('admin_author'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($t->text('admin_title_column'), ENT_QUOTES, 'UTF-8') ?></span>
          <input type="text" name="posts_q" value="<?= htmlspecialchars((string) $postsFilters['query'], ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
          <span><?= htmlspecialchars($t->text('admin_visibility'), ENT_QUOTES, 'UTF-8') ?></span>
          <select name="posts_visibility">
            <option value=""><?= htmlspecialchars($t->text('search_scope_all'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="public" <?= $postsFilters['visibility'] === 'public' ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_public'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="friends" <?= $postsFilters['visibility'] === 'friends' ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_friends'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="private" <?= $postsFilters['visibility'] === 'private' ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_private'), ENT_QUOTES, 'UTF-8') ?></option>
          </select>
        </label>
        <label>
          <span><?= htmlspecialchars($t->text('admin_post_type'), ENT_QUOTES, 'UTF-8') ?></span>
          <select name="posts_type">
            <option value=""><?= htmlspecialchars($t->text('search_scope_all'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="publication" <?= $postsFilters['post_type'] === 'publication' ? 'selected' : '' ?>><?= htmlspecialchars($t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="entrainement" <?= $postsFilters['post_type'] === 'entrainement' ? 'selected' : '' ?>><?= htmlspecialchars($t->text('posts_type_training'), ENT_QUOTES, 'UTF-8') ?></option>
          </select>
        </label>
        <div class="admin-filters__actions">
          <button type="submit"><?= htmlspecialchars($t->text('admin_apply_filters'), ENT_QUOTES, 'UTF-8') ?></button>
          <a href="<?= htmlspecialchars($buildAdminUrl(['posts_q' => null, 'posts_visibility' => null, 'posts_type' => null, 'posts_page' => null]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('admin_reset_filters'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
      </form>

      <?php if ($posts === []): ?>
        <p class="admin-empty"><?= htmlspecialchars($t->text('admin_no_posts_match'), ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th><?= htmlspecialchars($t->text('admin_author'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_post_type'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_details'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_visibility'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_action'), ENT_QUOTES, 'UTF-8') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($posts as $p): ?>
                <tr>
                  <td><?= (int) $p['id'] ?></td>
                  <td>
                    <a href="/user?username=<?= rawurlencode((string) $p['username']) ?>">
                      <?= htmlspecialchars((string) $p['username'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </td>
                  <td>
                    <span class="admin-tag"><?= htmlspecialchars(($p['post_type'] ?? 'publication') === 'entrainement' ? $t->text('posts_type_training') : $t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></span>
                  </td>
                  <td>
                    <div class="admin-meta">
                      <strong><?= htmlspecialchars($truncate((string) ($p['title'] ?? $t->text('post_untitled')), 64), ENT_QUOTES, 'UTF-8') ?></strong>
                      <span><?= htmlspecialchars($truncate((string) ($p['content'] ?? ''), 110), ENT_QUOTES, 'UTF-8') ?></span>
                      <?php if (!empty($p['location'])): ?>
                        <span><?= htmlspecialchars($t->text('post_location'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $p['location'], ENT_QUOTES, 'UTF-8') ?></span>
                      <?php endif; ?>
                      <span><?= htmlspecialchars($t->text('admin_created_at'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $p['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                  </td>
                  <td><?= htmlspecialchars((string) $p['visibility'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <div class="admin-actions">
                      <a class="admin-link-chip" href="/post?id=<?= (int) $p['id'] ?>"><?= htmlspecialchars($t->text('admin_open_post'), ENT_QUOTES, 'UTF-8') ?></a>
                      <form method="post" action="/admin/posts/delete" data-confirm="<?= htmlspecialchars($t->text('admin_delete_post_confirm'), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentRequestUri, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="post_id" value="<?= (int) $p['id'] ?>">
                        <button type="submit"><?= htmlspecialchars($t->text('admin_delete'), ENT_QUOTES, 'UTF-8') ?></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?= $renderPagination($postsPagination, 'posts_page') ?>
      <?php endif; ?>
    </section>

    <section class="admin-card">
      <div class="admin-card__head">
        <div>
          <p class="admin-card__eyebrow"><?= htmlspecialchars($t->text('admin_filters'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($t->text('admin_comments'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <p class="admin-card__summary"><?= (int) $commentsPagination['total'] ?> <?= htmlspecialchars($t->text('admin_results_summary'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <form class="admin-filters" method="get" action="/admin">
        <?= $renderHiddenInputs(['comments_q', 'comments_post_id', 'comments_page']) ?>
        <label>
          <span>ID / <?= htmlspecialchars($t->text('admin_author'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($t->text('admin_content'), ENT_QUOTES, 'UTF-8') ?></span>
          <input type="text" name="comments_q" value="<?= htmlspecialchars((string) $commentsFilters['query'], ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
          <span><?= htmlspecialchars($t->text('admin_post'), ENT_QUOTES, 'UTF-8') ?> ID</span>
          <input type="number" min="1" name="comments_post_id" value="<?= $commentsFilters['post_id'] > 0 ? (int) $commentsFilters['post_id'] : '' ?>">
        </label>
        <div class="admin-filters__actions">
          <button type="submit"><?= htmlspecialchars($t->text('admin_apply_filters'), ENT_QUOTES, 'UTF-8') ?></button>
          <a href="<?= htmlspecialchars($buildAdminUrl(['comments_q' => null, 'comments_post_id' => null, 'comments_page' => null]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('admin_reset_filters'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
      </form>

      <?php if ($comments === []): ?>
        <p class="admin-empty"><?= htmlspecialchars($t->text('admin_no_comments_match'), ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th><?= htmlspecialchars($t->text('admin_post'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_author'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_content'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_created_at'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_action'), ENT_QUOTES, 'UTF-8') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($comments as $c): ?>
                <tr>
                  <td><?= (int) $c['id'] ?></td>
                  <td>
                    <div class="admin-meta">
                      <a href="/post?id=<?= (int) $c['post_id'] ?>">#<?= (int) $c['post_id'] ?></a>
                      <span><?= htmlspecialchars($truncate((string) ($c['post_title'] ?? ''), 72), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                  </td>
                  <td>
                    <a href="/user?username=<?= rawurlencode((string) $c['username']) ?>">
                      <?= htmlspecialchars((string) $c['username'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </td>
                  <td><?= htmlspecialchars($truncate((string) $c['content'], 120), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $c['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <div class="admin-actions">
                      <a class="admin-link-chip" href="/post?id=<?= (int) $c['post_id'] ?>"><?= htmlspecialchars($t->text('admin_open_post_short'), ENT_QUOTES, 'UTF-8') ?></a>
                      <form method="post" action="/admin/comments/delete" data-confirm="<?= htmlspecialchars($t->text('admin_delete_comment_confirm'), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentRequestUri, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                        <button type="submit"><?= htmlspecialchars($t->text('admin_delete'), ENT_QUOTES, 'UTF-8') ?></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?= $renderPagination($commentsPagination, 'comments_page') ?>
      <?php endif; ?>
    </section>

    <section class="admin-card">
      <div class="admin-card__head">
        <div>
          <p class="admin-card__eyebrow"><?= htmlspecialchars($t->text('admin_filters'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($t->text('admin_log'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <p class="admin-card__summary"><?= (int) $logsPagination['total'] ?> <?= htmlspecialchars($t->text('admin_results_summary'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <form class="admin-filters" method="get" action="/admin">
        <?= $renderHiddenInputs(['logs_q', 'logs_action', 'logs_target_type', 'logs_page']) ?>
        <label>
          <span><?= htmlspecialchars($t->text('admin_log_details'), ENT_QUOTES, 'UTF-8') ?></span>
          <input type="text" name="logs_q" value="<?= htmlspecialchars((string) $logsFilters['query'], ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
          <span><?= htmlspecialchars($t->text('admin_action'), ENT_QUOTES, 'UTF-8') ?></span>
          <select name="logs_action">
            <?php foreach ($logActionOptions as $value => $label): ?>
              <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $logsFilters['action'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          <span><?= htmlspecialchars($t->text('admin_target'), ENT_QUOTES, 'UTF-8') ?></span>
          <select name="logs_target_type">
            <?php foreach ($logTargetOptions as $value => $label): ?>
              <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $logsFilters['target_type'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <div class="admin-filters__actions">
          <button type="submit"><?= htmlspecialchars($t->text('admin_apply_filters'), ENT_QUOTES, 'UTF-8') ?></button>
          <a href="<?= htmlspecialchars($buildAdminUrl(['logs_q' => null, 'logs_action' => null, 'logs_target_type' => null, 'logs_page' => null]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t->text('admin_reset_filters'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
      </form>

      <?php if ($logs === []): ?>
        <p class="admin-empty"><?= htmlspecialchars($t->text('admin_no_logs_match'), ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table>
            <thead>
              <tr>
                <th><?= htmlspecialchars($t->text('admin_date'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_administrator'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_action'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_target'), ENT_QUOTES, 'UTF-8') ?></th>
                <th><?= htmlspecialchars($t->text('admin_log_details'), ENT_QUOTES, 'UTF-8') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
                <?php $logDetails = $formatLogDetails($log); ?>
                <tr>
                  <td><?= htmlspecialchars((string) $log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <a href="/user?username=<?= rawurlencode((string) $log['admin_username']) ?>">
                      <?= htmlspecialchars((string) $log['admin_username'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </td>
                  <td><?= htmlspecialchars($formatLogAction($log), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($formatLogTarget($log), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php if ($logDetails === []): ?>
                      <span class="admin-empty-inline">—</span>
                    <?php else: ?>
                      <ul class="admin-log-details">
                        <?php foreach ($logDetails as $detail): ?>
                          <li>
                            <span><?= htmlspecialchars((string) $detail['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            <strong><?= htmlspecialchars((string) $detail['value'], ENT_QUOTES, 'UTF-8') ?></strong>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?= $renderPagination($logsPagination, 'logs_page') ?>
      <?php endif; ?>
      <p class="admin-note"><?= htmlspecialchars($t->text('admin_log_note'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
