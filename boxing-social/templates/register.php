<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Inscription</title></head>
<body>
  <h1>Inscription</h1>

  <?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
      <p style="color:#b42318;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>
  <?php endif; ?>

  <form method="post" action="/register">
    <input name="username" placeholder="Pseudo" required value="<?= htmlspecialchars((string)($old['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input name="email" type="email" placeholder="Email" required value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input name="password" type="password" placeholder="Mot de passe" required>
    <input name="password_confirm" type="password" placeholder="Confirmer le mot de passe" required>
    <button type="submit">Creer mon compte</button>
  </form>

  <a href="/login">Deja un compte ? Connexion</a>
</body>
</html>
