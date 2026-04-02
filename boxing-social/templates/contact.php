<?php require dirname(__DIR__) . '/templates/partials/app-locale.php'; ?>
<?php $isLoggedIn = isset($_SESSION['user']['id']); ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($t->text('contact_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/contact-public.css?v=20260315i">
  <link rel="stylesheet" href="/css/scroll-top.css?v=20260317a">
  <?php if (($recaptchaSiteKey ?? '') !== ''): ?>
    <script src="https://www.google.com/recaptcha/api.js?hl=<?= htmlspecialchars((string) $htmlLang, ENT_QUOTES, 'UTF-8') ?>" async defer></script>
  <?php endif; ?>
  <?php if (!empty($useClientFirebaseFallback)): ?>
    <script type="module" src="/js/contact-firestore.js?v=20260320a"></script>
  <?php endif; ?>
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

      <?php if ($success !== ''): ?>
        <p class="msg-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if ($errors !== []): ?>
        <?php foreach ($errors as $error): ?>
          <p class="msg-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (!empty($useClientFirebaseFallback)): ?>
        <p class="msg-success" data-contact-success hidden></p>
        <p class="msg-error" data-contact-error hidden></p>
      <?php endif; ?>

      <form
        class="contact-public-form"
        method="post"
        action="/contact"
        data-contact-form
        <?= !empty($useClientFirebaseFallback) ? 'data-contact-client-form data-contact-mode="firebase-client"' : 'data-contact-mode="server"' ?>
        data-firebase-api-key="<?= htmlspecialchars((string) ($firebaseClientConfig['apiKey'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        data-firebase-auth-domain="<?= htmlspecialchars((string) ($firebaseClientConfig['authDomain'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        data-firebase-project-id="<?= htmlspecialchars((string) ($firebaseClientConfig['projectId'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        data-firebase-storage-bucket="<?= htmlspecialchars((string) ($firebaseClientConfig['storageBucket'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        data-firebase-messaging-sender-id="<?= htmlspecialchars((string) ($firebaseClientConfig['messagingSenderId'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        data-firebase-app-id="<?= htmlspecialchars((string) ($firebaseClientConfig['appId'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        data-firebase-measurement-id="<?= htmlspecialchars((string) ($firebaseClientConfig['measurementId'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        data-contact-success-message="<?= htmlspecialchars($t->text('contact_success_sent'), ENT_QUOTES, 'UTF-8') ?>"
        data-contact-error-config="Configuration Firestore incomplète."
        data-contact-error-invalid="<?= htmlspecialchars($t->text('contact_error_message_short'), ENT_QUOTES, 'UTF-8') ?>"
        data-contact-error-recaptcha="<?= htmlspecialchars($t->text('contact_error_recaptcha_required'), ENT_QUOTES, 'UTF-8') ?>"
        data-contact-send-label="<?= htmlspecialchars($t->text('contact_send'), ENT_QUOTES, 'UTF-8') ?>"
        data-contact-sending-label="Envoi..."
      >
        <div class="contact-public-form__honeypot" aria-hidden="true">
          <label>
            Website
            <input type="text" name="website" tabindex="-1" autocomplete="off">
          </label>
        </div>

        <label>
          <?= htmlspecialchars($t->text('contact_email'), ENT_QUOTES, 'UTF-8') ?>
          <input type="email" name="email" placeholder="ton@email.com" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="254" autocomplete="email" inputmode="email" pattern="<?= htmlspecialchars(\App\Core\InputValidator::EMAIL_HTML_PATTERN, ENT_QUOTES, 'UTF-8') ?>" required>
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
          <textarea name="message" rows="7" minlength="20" maxlength="4000" placeholder="<?= htmlspecialchars($t->text('contact_message_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required><?= htmlspecialchars((string) ($old['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <?php if (($recaptchaSiteKey ?? '') !== ''): ?>
          <div class="contact-public-form__recaptcha">
            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars((string) $recaptchaSiteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
          </div>
        <?php endif; ?>

        <button type="submit" data-contact-submit><?= htmlspecialchars($t->text('contact_send'), ENT_QUOTES, 'UTF-8') ?></button>
      </form>

      <p class="contact-public-rights">&copy; <?= htmlspecialchars($t->text('footer_rights'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/cookie-notice.php'; ?>
  <?php require dirname(__DIR__) . '/templates/partials/scroll-top.php'; ?>
</body>
</html>
