<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Connexion</title>
  <link rel="stylesheet" href="/css/auth-login.css">
</head>
<body>
  <main class="page">
    <h1>Connexion</h1>

    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" action="/login">
      <input name="email" type="email" placeholder="Email" required value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <input name="password" type="password" placeholder="Mot de passe" required>
      <button type="submit">Se connecter</button>
    </form>

    <a class="link" href="/register">Creer un compte</a>
  </main>
</body>
</html>
