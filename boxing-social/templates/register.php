<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Inscription</title>
  <link rel="stylesheet" href="/css/auth-register.css">
  <link rel="stylesheet" href="/css/scroll-top.css?v=20260314a">
</head>
<body>
  <main class="page">
    <h1>Inscription</h1>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" action="/register">
      <input name="username" placeholder="Pseudo" required value="<?= htmlspecialchars((string)($old['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <input name="email" type="email" placeholder="Email" required value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <input name="password" type="password" placeholder="Mot de passe" required>
      <input name="password_confirm" type="password" placeholder="Confirmer le mot de passe" required>
      <button type="submit">Creer mon compte</button>
    </form>

    <a class="link" href="/login">Deja un compte ? Connexion</a>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/scroll-top.php'; ?>
</body>
</html>
