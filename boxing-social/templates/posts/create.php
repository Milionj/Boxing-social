<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Creer un post</title>
</head>
<body>
  <h1>Creer un post</h1>

  <p>
    <a href="/posts">Retour feed</a> |
    <a href="/profile">Mon profil</a>
  </p>

  <?php if (!empty($success)): ?>
    <p style="color:#067647;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
      <p style="color:#b42318;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>
  <?php endif; ?>

  <form method="post" action="/posts" enctype="multipart/form-data">
    <p>
      <input type="text" name="title" placeholder="Titre (optionnel)">
    </p>

    <p>
      <textarea name="content" rows="6" cols="60" placeholder="Contenu du post" required></textarea>
    </p>

    <p>
      <input type="text" name="location" placeholder="Lieu (optionnel)">
    </p>

    <p>
      <select name="visibility">
        <option value="public">Public</option>
        <option value="friends">Amis</option>
        <option value="private">Prive</option>
      </select>
    </p>

    <p>
      <input type="file" name="post_image" accept=".jpg,.jpeg,.png,.webp">
    </p>

    <button type="submit">Publier</button>
  </form>
</body>
</html>
