<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Profil</title>
</head>
<body>
  <h1>Mon profil</h1>

  <?php if (!empty($success)): ?>
    <p style="color:#067647;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
      <p style="color:#b42318;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>
  <?php endif; ?>

  <p><strong>ID:</strong> <?= (int) $user['id'] ?></p>
  <p><strong>Role:</strong> <?= htmlspecialchars((string) $user['role'], ENT_QUOTES, 'UTF-8') ?></p>

  <form method="post" action="/profile">
    <input name="username" required value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
    <input name="email" type="email" required value="<?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?>">
    <textarea name="bio" rows="5" cols="40"><?= htmlspecialchars((string) ($user['bio'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
    <button type="submit">Enregistrer</button>
  </form>

  <form method="post" action="/logout" style="margin-top:12px;">
    <button type="submit">Se deconnecter</button>
  </form>

  <p><a href="/">Accueil</a></p>
</body>
</html>
