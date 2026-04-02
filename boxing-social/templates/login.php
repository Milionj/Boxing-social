<!doctype html>
<?php $loggedOut = (($_GET['logged_out'] ?? '') === '1'); ?>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion</title>
  <link rel="stylesheet" href="/css/auth.css?v=20260315i">
  <link rel="stylesheet" href="/css/scroll-top.css?v=20260317a">
  <?php if (($recaptchaSiteKey ?? '') !== ''): ?>
    <script src="https://www.google.com/recaptcha/api.js?hl=fr" async defer></script>
  <?php endif; ?>
</head>
<body class="auth-page"<?= $loggedOut ? ' data-logout-cleanup="1"' : '' ?>>
  <main class="auth-layout">
    <section class="auth-panel auth-panel--brand">
      <a class="auth-brand" href="/">
        <img src="/img/Bonlogo.png" alt="Logo Boxing Social">
        <span class="auth-brand__copy">
          <strong>Boxing Social</strong>
          <small>Communauté boxe</small>
        </span>
      </a>

      <div class="auth-points">
        <article>
          <strong>Fil principal</strong>
          <p>Retrouve rapidement les publications, les commentaires et les séances d’entraînement.</p>
        </article>
        <article>
          <strong>Messagerie</strong>
          <p>Reprends une conversation en cours ou rouvre un échange avec un membre.</p>
        </article>
      </div>
    </section>

    <section class="auth-panel auth-panel--form">
      <div class="auth-card-head">
        <h2>Connexion</h2>
        <p>Entre ton email et ton mot de passe pour accéder à ton espace.</p>
      </div>

      <?php if (!empty($success)): ?>
        <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
          <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>
      <?php endif; ?>

      <form class="auth-form" method="post" action="/login">
        <label class="auth-field">
          <span>Email</span>
          <input name="email" type="email" placeholder="ton@email.com" maxlength="254" autocomplete="email" inputmode="email" pattern="<?= htmlspecialchars(\App\Core\InputValidator::EMAIL_HTML_PATTERN, ENT_QUOTES, 'UTF-8') ?>" required value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="auth-field">
          <span>Mot de passe</span>
          <input name="password" type="password" placeholder="Mot de passe" autocomplete="current-password" minlength="12" required>
        </label>

        <?php if (($recaptchaSiteKey ?? '') !== ''): ?>
          <div class="auth-recaptcha">
            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars((string) $recaptchaSiteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
          </div>
        <?php endif; ?>

        <button type="submit">Se connecter</button>
      </form>

      <?php require dirname(__DIR__) . '/templates/partials/form-privacy-link.php'; ?>

      <p class="auth-switch">
        Pas encore de compte ?
        <a href="/register">Créer un compte</a>
      </p>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/cookie-notice.php'; ?>
  <?php require dirname(__DIR__) . '/templates/partials/scroll-top.php'; ?>
</body>
</html>
