<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Creer un post</title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/posts-create.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <h1>Creer un post</h1>
    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" action="/posts" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Titre (optionnel)">
      <textarea name="content" rows="6" cols="60" placeholder="Contenu du post" required></textarea>
      <input type="text" name="location" placeholder="Lieu (optionnel)">

      <select name="visibility">
        <option value="public">Public</option>
        <option value="friends">Amis</option>
        <option value="private">Prive</option>
      </select>

      <input type="file" name="post_image" accept=".jpg,.jpeg,.png,.webp">
      <button type="submit">Publier</button>
    </form>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
