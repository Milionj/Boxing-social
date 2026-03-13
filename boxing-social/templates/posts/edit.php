<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('posts_edit_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/posts-edit.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <h1><?= htmlspecialchars($t->text('posts_edit_heading'), ENT_QUOTES, 'UTF-8') ?></h1>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" action="/posts/update">
      <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
      <select name="post_type" required>
        <option value="publication" <?= (($post['post_type'] ?? 'publication') === 'publication') ? 'selected' : '' ?>>Publication simple</option>
        <option value="entrainement" <?= (($post['post_type'] ?? 'publication') === 'entrainement') ? 'selected' : '' ?>>Declaration de seance</option>
      </select>
      <input type="text" name="title" value="<?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <textarea name="content" rows="6" cols="60" required><?= htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
      <input type="text" name="location" value="<?= htmlspecialchars((string) ($post['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <input
        type="datetime-local"
        name="scheduled_at"
        value="<?= !empty($post['scheduled_at']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime((string) $post['scheduled_at'])), ENT_QUOTES, 'UTF-8') : '' ?>"
      >

      <select name="visibility">
        <option value="public" <?= ($post['visibility'] === 'public') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_public'), ENT_QUOTES, 'UTF-8') ?></option>
        <option value="friends" <?= ($post['visibility'] === 'friends') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_friends'), ENT_QUOTES, 'UTF-8') ?></option>
        <option value="private" <?= ($post['visibility'] === 'private') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_private'), ENT_QUOTES, 'UTF-8') ?></option>
      </select>

      <button type="submit"><?= htmlspecialchars($t->text('posts_save'), ENT_QUOTES, 'UTF-8') ?></button>
    </form>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
