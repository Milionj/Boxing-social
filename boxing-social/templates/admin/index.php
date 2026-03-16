<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('admin_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260315o">
  <link rel="stylesheet" href="/css/admin-index.css?v=20260315i">
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
        <strong><?= count($users) ?></strong>
      </article>
      <article class="admin-stat-card">
        <span><?= htmlspecialchars($t->text('admin_stats_posts'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong><?= count($posts) ?></strong>
      </article>
      <article class="admin-stat-card">
        <span><?= htmlspecialchars($t->text('admin_stats_comments'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong><?= count($comments) ?></strong>
      </article>
      <article class="admin-stat-card">
        <span><?= htmlspecialchars($t->text('admin_stats_logs'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong><?= count($logs) ?></strong>
      </article>
    </section>

    <section class="admin-card">
      <div class="admin-card__head">
        <div>
          <h2><?= htmlspecialchars($t->text('admin_users'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
      </div>
      <div class="admin-table-wrap">
        <table>
          <thead>
            <tr><th>ID</th><th>Pseudo</th><th><?= htmlspecialchars($t->text('admin_email'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_role'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_status'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_action'), ENT_QUOTES, 'UTF-8') ?></th></tr>
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
                  <form method="post" action="/admin/users/toggle">
                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                    <input type="hidden" name="is_active" value="<?= ((int) $u['is_active'] === 1) ? '0' : '1' ?>">
                    <button type="submit"><?= ((int) $u['is_active'] === 1) ? htmlspecialchars($t->text('admin_disable'), ENT_QUOTES, 'UTF-8') : htmlspecialchars($t->text('admin_enable'), ENT_QUOTES, 'UTF-8') ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="admin-card">
      <div class="admin-card__head">
        <div>
          <h2><?= htmlspecialchars($t->text('admin_posts'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
      </div>
      <div class="admin-table-wrap">
        <table>
          <thead>
            <tr><th>ID</th><th><?= htmlspecialchars($t->text('admin_author'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_title_column'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_visibility'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_action'), ENT_QUOTES, 'UTF-8') ?></th></tr>
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
                <td><?= htmlspecialchars((string) ($p['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $p['visibility'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <form method="post" action="/admin/posts/delete" onsubmit="return confirm('<?= htmlspecialchars($t->text('admin_delete_post_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                    <input type="hidden" name="post_id" value="<?= (int) $p['id'] ?>">
                    <button type="submit"><?= htmlspecialchars($t->text('admin_delete'), ENT_QUOTES, 'UTF-8') ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="admin-card">
      <div class="admin-card__head">
        <div>
          <h2><?= htmlspecialchars($t->text('admin_comments'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
      </div>
      <div class="admin-table-wrap">
        <table>
          <thead>
            <tr><th>ID</th><th><?= htmlspecialchars($t->text('admin_post'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_author'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_content'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_action'), ENT_QUOTES, 'UTF-8') ?></th></tr>
          </thead>
          <tbody>
            <?php foreach ($comments as $c): ?>
              <tr>
                <td><?= (int) $c['id'] ?></td>
                <td>#<?= (int) $c['post_id'] ?></td>
                <td>
                  <a href="/user?username=<?= rawurlencode((string) $c['username']) ?>">
                    <?= htmlspecialchars((string) $c['username'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td><?= htmlspecialchars((string) $c['content'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <form method="post" action="/admin/comments/delete" onsubmit="return confirm('<?= htmlspecialchars($t->text('admin_delete_comment_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                    <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                    <button type="submit"><?= htmlspecialchars($t->text('admin_delete'), ENT_QUOTES, 'UTF-8') ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="admin-card">
      <div class="admin-card__head">
        <div>
          <h2><?= htmlspecialchars($t->text('admin_log'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
      </div>
      <?php if (empty($logs)): ?>
        <p class="admin-empty"><?= htmlspecialchars($t->text('admin_log_empty'), ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table>
            <thead>
              <tr><th><?= htmlspecialchars($t->text('admin_date'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_administrator'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_action'), ENT_QUOTES, 'UTF-8') ?></th><th><?= htmlspecialchars($t->text('admin_target'), ENT_QUOTES, 'UTF-8') ?></th></tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
                <tr>
                  <td><?= htmlspecialchars((string) $log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <a href="/user?username=<?= rawurlencode((string) $log['admin_username']) ?>">
                      <?= htmlspecialchars((string) $log['admin_username'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </td>
                  <td><?= htmlspecialchars((string) $log['action'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $log['target_type'], ENT_QUOTES, 'UTF-8') ?> #<?= (int) ($log['target_id'] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      <p class="admin-note"><?= htmlspecialchars($t->text('admin_log_note'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
