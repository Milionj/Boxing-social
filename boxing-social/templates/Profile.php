<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Profil</title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/profile.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <h1>Mon profil</h1>

    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
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

    <form class="row" method="post" action="/logout">
      <button type="submit">Se deconnecter</button>
    </form>

    <?php $errorsPassword = $_SESSION['errors_password'] ?? []; ?>
    <?php $successPassword = $_SESSION['success_password'] ?? ''; ?>
    <?php unset($_SESSION['errors_password'], $_SESSION['success_password']); ?>

    <?php $errorsAvatar = $_SESSION['errors_avatar'] ?? []; ?>
    <?php $successAvatar = $_SESSION['success_avatar'] ?? ''; ?>
    <?php unset($_SESSION['errors_avatar'], $_SESSION['success_avatar']); ?>

    <hr>
    <h2>Photo de profil</h2>

    <?php if (!empty($user['avatar_path'])): ?>
      <p>
        <img class="avatar" src="<?= htmlspecialchars((string) $user['avatar_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
      </p>
    <?php endif; ?>

    <?php if (!empty($successAvatar)): ?>
      <p class="msg-success"><?= htmlspecialchars($successAvatar, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errorsAvatar)): ?>
      <?php foreach ($errorsAvatar as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" action="/profile/avatar" enctype="multipart/form-data">
      <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" required>
      <button type="submit">Mettre a jour la photo</button>
    </form>

    <hr>
    <h2>Changer le mot de passe</h2>

    <?php if (!empty($successPassword)): ?>
      <p class="msg-success"><?= htmlspecialchars($successPassword, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errorsPassword)): ?>
      <?php foreach ($errorsPassword as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" action="/profile/password">
      <input type="password" name="current_password" placeholder="Mot de passe actuel" required>
      <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
      <input type="password" name="confirm_password" placeholder="Confirmer le nouveau mot de passe" required>
      <button type="submit">Mettre a jour le mot de passe</button>
    </form>
  </main>
  <?php require dirname(__DIR__) . '/partials/app-footer.php'; ?>
</body>
</html>
