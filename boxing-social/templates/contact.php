<?php require dirname(__DIR__) . '/templates/partials/app-locale.php'; ?>
<?php $isLoggedIn = isset($_SESSION['user']['id']); ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($t->text('contact_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/contact-public.css?v=20260315i">
  <link rel="stylesheet" href="/css/scroll-top.css?v=20260315i">
  <script>
    window.BOXING_SOCIAL_FIREBASE_CONFIG = {
      apiKey: <?= json_encode((string) ($_ENV['FIREBASE_API_KEY'] ?? ''), JSON_UNESCAPED_SLASHES) ?>,
      authDomain: <?= json_encode((string) ($_ENV['FIREBASE_AUTH_DOMAIN'] ?? ''), JSON_UNESCAPED_SLASHES) ?>,
      projectId: <?= json_encode((string) ($_ENV['FIREBASE_PROJECT_ID'] ?? ''), JSON_UNESCAPED_SLASHES) ?>,
      storageBucket: <?= json_encode((string) ($_ENV['FIREBASE_STORAGE_BUCKET'] ?? ''), JSON_UNESCAPED_SLASHES) ?>,
      messagingSenderId: <?= json_encode((string) ($_ENV['FIREBASE_MESSAGING_SENDER_ID'] ?? ''), JSON_UNESCAPED_SLASHES) ?>,
      appId: <?= json_encode((string) ($_ENV['FIREBASE_APP_ID'] ?? ''), JSON_UNESCAPED_SLASHES) ?>,
      measurementId: <?= json_encode((string) ($_ENV['FIREBASE_MEASUREMENT_ID'] ?? ''), JSON_UNESCAPED_SLASHES) ?>
    };
  </script>
  <script type="module" src="/js/contact-firestore.js"></script>
</head>
<body class="contact-public-page">
  <main class="contact-public-shell">
    <header class="contact-public-topbar">
      <a class="contact-public-brand" href="/">
        <img src="/img/Bonlogo.png" alt="Logo Boxing Social">
        <span>
          <strong>Boxing Social</strong>
          <small>Communauté boxe</small>
        </span>
      </a>

      <nav class="contact-public-nav" aria-label="Navigation publique">
        <a href="/"><?= $isLoggedIn ? 'Retour à l’accueil' : 'Accueil' ?></a>
        <a href="/privacy"><?= htmlspecialchars($t->text('nav_privacy'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php if (!$isLoggedIn): ?>
          <a href="/login">Connexion</a>
          <a class="contact-public-nav__cta" href="/register">Inscription</a>
        <?php endif; ?>
      </nav>
    </header>

    <section class="contact-public-hero">
      <p class="contact-public-kicker"><?= htmlspecialchars($t->text('contact_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
      <h1><?= htmlspecialchars($t->text('contact_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('contact_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <section class="contact-public-card">
      <div class="contact-public-card__head">
        <div>
          <p class="contact-public-card__eyebrow"><?= htmlspecialchars($t->text('contact_form_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($t->text('contact_form_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
          <p><?= htmlspecialchars($t->text('contact_form_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>

      <p class="msg-success" data-contact-success hidden></p>
      <p class="msg-error" data-contact-error hidden></p>

      <form class="contact-public-form" method="post" action="/contact" data-contact-form>
        <label>
          <?= htmlspecialchars($t->text('contact_email'), ENT_QUOTES, 'UTF-8') ?>
          <input type="email" name="email" placeholder="ton@email.com" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </label>

        <label>
          <?= htmlspecialchars($t->text('contact_subject'), ENT_QUOTES, 'UTF-8') ?>
          <select name="subject" required>
            <option value="question_generale" <?= (($old['subject'] ?? '') === 'question_generale') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('contact_subject_general'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="support_technique" <?= (($old['subject'] ?? '') === 'support_technique') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('contact_subject_support'), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="signalement" <?= (($old['subject'] ?? '') === 'signalement') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('contact_subject_report'), ENT_QUOTES, 'UTF-8') ?></option>
          </select>
        </label>

        <label>
          <?= htmlspecialchars($t->text('contact_message'), ENT_QUOTES, 'UTF-8') ?>
          <textarea name="message" rows="7" placeholder="<?= htmlspecialchars($t->text('contact_message_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required><?= htmlspecialchars((string) ($old['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <button type="submit" data-contact-submit><?= htmlspecialchars($t->text('contact_send'), ENT_QUOTES, 'UTF-8') ?></button>
      </form>

      <p class="contact-public-rights">&copy; <?= htmlspecialchars($t->text('footer_rights'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/cookie-notice.php'; ?>
  <?php require dirname(__DIR__) . '/templates/partials/scroll-top.php'; ?>
</body>
</html>
