<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscription</title>
  <link rel="stylesheet" href="/css/auth.css?v=20260315i">
  <link rel="stylesheet" href="/css/scroll-top.css?v=20260315i">
</head>
<body class="auth-page">
  <main class="auth-layout">
    <section class="auth-panel auth-panel--brand">
      <a class="auth-brand" href="/">
        <img src="/img/Bonlogo.png" alt="Logo Boxing Social">
        <span class="auth-brand__copy">
          <strong>Boxing Social</strong>
          <small>Communauté boxe</small>
        </span>
      </a>

      <div class="auth-hero">
        <p class="auth-eyebrow">Inscription</p>
        <h1>Créer ton compte</h1>
        <p>Rejoins la communauté pour publier, chercher des partenaires et suivre les séances proposées.</p>
      </div>

      <div class="auth-points">
        <article>
          <strong>Profil public</strong>
          <p>Présente ta pratique, ton niveau et ce que tu recherches dans la communauté.</p>
        </article>
        <article>
          <strong>Interactions</strong>
          <p>Ajoute des amis, commente les posts et manifeste ton intérêt pour les séances.</p>
        </article>
      </div>
    </section>

    <section class="auth-panel auth-panel--form">
      <div class="auth-card-head">
        <h2>Inscription</h2>
        <p>Renseigne les informations de base pour ouvrir ton compte Boxing Social.</p>
      </div>

      <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
          <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>
      <?php endif; ?>

      <form class="auth-form" method="post" action="/register">
        <label class="auth-field">
          <span>Pseudo</span>
          <input name="username" placeholder="Pseudo" required value="<?= htmlspecialchars((string)($old['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="auth-field">
          <span>Email</span>
          <input name="email" type="email" placeholder="ton@email.com" required value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="auth-field">
          <span>Mot de passe</span>
          <input name="password" type="password" placeholder="Mot de passe" required>
        </label>

        <label class="auth-field">
          <span>Confirmer le mot de passe</span>
          <input name="password_confirm" type="password" placeholder="Confirmer le mot de passe" required>
        </label>

        <button type="submit">Créer mon compte</button>
      </form>

      <?php require dirname(__DIR__) . '/templates/partials/form-privacy-link.php'; ?>

      <p class="auth-switch">
        Déjà un compte ?
        <a href="/login">Connexion</a>
      </p>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/cookie-notice.php'; ?>
  <?php require dirname(__DIR__) . '/templates/partials/scroll-top.php'; ?>
</body>
</html>
