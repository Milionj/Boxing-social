<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Modifier post</title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/posts-edit.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <h1>Modifier post</h1>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" action="/posts/update">
      <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
      <input type="text" name="title" value="<?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <textarea name="content" rows="6" cols="60" required><?= htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
      <input type="text" name="location" value="<?= htmlspecialchars((string) ($post['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

      <select name="visibility">
        <option value="public" <?= ($post['visibility'] === 'public') ? 'selected' : '' ?>>Public</option>
        <option value="friends" <?= ($post['visibility'] === 'friends') ? 'selected' : '' ?>>Amis</option>
        <option value="private" <?= ($post['visibility'] === 'private') ? 'selected' : '' ?>>Prive</option>
      </select>

      <button type="submit">Enregistrer</button>
    </form>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
