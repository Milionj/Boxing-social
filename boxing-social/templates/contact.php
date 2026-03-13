<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Contact</title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/static-page.css">
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
<body class="app-shell">
  <?php require dirname(__DIR__) . '/templates/partials/app-navbar.php'; ?>
  <main class="static-page app-main">
    <section class="static-hero">
      <p class="static-eyebrow">Contact</p>
      <h1>Entrer en contact avec Boxing Social</h1>
      <p>Une question sur la communaute, un signalement ou un besoin d aide sur le reseau social. Utilise ce formulaire pour nous ecrire.</p>
    </section>

    <section class="static-card">
      <p class="msg-success" data-contact-success hidden></p>
      <p class="msg-error" data-contact-error hidden></p>

      <form class="contact-form" method="post" action="/contact" data-contact-form>
        <label>
          Email
          <input type="email" name="email" placeholder="ton@email.com" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </label>

        <label>
          Sujet
          <select name="subject" required>
            <option value="question_generale" <?= (($old['subject'] ?? '') === 'question_generale') ? 'selected' : '' ?>>Question generale</option>
            <option value="support_technique" <?= (($old['subject'] ?? '') === 'support_technique') ? 'selected' : '' ?>>Support technique</option>
            <option value="signalement" <?= (($old['subject'] ?? '') === 'signalement') ? 'selected' : '' ?>>Signalement</option>
          </select>
        </label>

        <label>
          Message
          <textarea name="message" rows="7" placeholder="Explique ton besoin..." required><?= htmlspecialchars((string) ($old['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <button type="submit" data-contact-submit>Envoyer</button>
      </form>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
