<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('posts_create_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/posts-create.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <h1><?= htmlspecialchars($t->text('posts_create_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" action="/posts" enctype="multipart/form-data">
      <select name="post_type" required>
        <option value="publication">Publication simple</option>
        <option value="entrainement">Declaration de seance</option>
      </select>
      <input type="text" name="title" placeholder="<?= htmlspecialchars($t->text('posts_title_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
      <textarea name="content" rows="6" cols="60" placeholder="<?= htmlspecialchars($t->text('posts_content_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
      <input type="text" name="location" placeholder="<?= htmlspecialchars($t->text('posts_location_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
      <input type="datetime-local" name="scheduled_at">

      <select name="visibility">
        <option value="public"><?= htmlspecialchars($t->text('visibility_public'), ENT_QUOTES, 'UTF-8') ?></option>
        <option value="friends"><?= htmlspecialchars($t->text('visibility_friends'), ENT_QUOTES, 'UTF-8') ?></option>
        <option value="private"><?= htmlspecialchars($t->text('visibility_private'), ENT_QUOTES, 'UTF-8') ?></option>
      </select>

      <input type="file" name="post_image" accept=".jpg,.jpeg,.png,.webp">
      <button type="submit"><?= htmlspecialchars($t->text('posts_publish'), ENT_QUOTES, 'UTF-8') ?></button>
    </form>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
