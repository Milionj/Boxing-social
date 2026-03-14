<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('posts_edit_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260314b">
  <link rel="stylesheet" href="/css/posts-edit.css?v=20260313n">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="post-edit-page app-main">
    <section class="post-edit-hero">
      <h1><?= htmlspecialchars($t->text('posts_edit_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('posts_edit_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <section class="post-edit-card">
      <div class="post-edit-card__head">
        <div>
          <h2><?= htmlspecialchars($t->text('posts_edit_form_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
          <p><?= htmlspecialchars($t->text('posts_edit_note'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

      <form class="post-edit-form" method="post" action="/posts/update">
        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">

        <div class="post-edit-grid">
          <label class="post-edit-field">
            <span><?= htmlspecialchars($t->text('posts_type_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="post_type" required>
              <option value="publication" <?= (($post['post_type'] ?? 'publication') === 'publication') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="entrainement" <?= (($post['post_type'] ?? 'publication') === 'entrainement') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('posts_type_training'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>

          <label class="post-edit-field">
            <span><?= htmlspecialchars($t->text('posts_visibility_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="visibility">
              <option value="public" <?= ($post['visibility'] === 'public') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_public'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="friends" <?= ($post['visibility'] === 'friends') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_friends'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="private" <?= ($post['visibility'] === 'private') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_private'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>
        </div>

        <label class="post-edit-field">
          <span><?= htmlspecialchars($t->text('posts_title_label'), ENT_QUOTES, 'UTF-8') ?></span>
          <input type="text" name="title" value="<?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t->text('posts_title_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="post-edit-field">
          <span><?= htmlspecialchars($t->text('posts_content_label'), ENT_QUOTES, 'UTF-8') ?></span>
          <textarea name="content" rows="7" required placeholder="<?= htmlspecialchars($t->text('posts_content_placeholder'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <div class="post-edit-grid">
          <label class="post-edit-field">
            <span><?= htmlspecialchars($t->text('posts_location_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="text" name="location" value="<?= htmlspecialchars((string) ($post['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t->text('posts_location_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
          </label>

          <label class="post-edit-field">
            <span><?= htmlspecialchars($t->text('posts_scheduled_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <input
              type="datetime-local"
              name="scheduled_at"
              value="<?= !empty($post['scheduled_at']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime((string) $post['scheduled_at'])), ENT_QUOTES, 'UTF-8') : '' ?>"
            >
          </label>
        </div>

        <div class="post-edit-actions">
          <button type="submit"><?= htmlspecialchars($t->text('posts_save'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
      </form>
    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
