<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Parametres</title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/static-page.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/templates/partials/app-navbar.php'; ?>
  <main class="static-page app-main">
    <section class="static-hero">
      <p class="static-eyebrow">Parametres</p>
      <h1>Preferences de compte</h1>
      <p>Regle ici les preferences generales de ton experience sur le reseau social.</p>
    </section>

    <section class="static-card settings-grid">
      <div class="settings-menu">
        <button type="button" class="is-active">General</button>
      </div>

      <div>
        <?php if (!empty($success)): ?>
          <p class="msg-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <?php foreach ($errors as $error): ?>
            <p class="msg-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!$tableReady): ?>
          <p class="static-note">La sauvegarde est en attente. Lance d abord la migration SQL `migrations/2026_03_13_create_user_settings.sql`.</p>
        <?php endif; ?>

        <form class="settings-list" method="post" action="/settings">
          <label class="setting-row">
            <span>Theme</span>
            <select name="theme">
              <option value="systeme" <?= ($settings['theme'] === 'systeme') ? 'selected' : '' ?>>Systeme</option>
              <option value="clair" <?= ($settings['theme'] === 'clair') ? 'selected' : '' ?>>Clair</option>
              <option value="sombre" <?= ($settings['theme'] === 'sombre') ? 'selected' : '' ?>>Sombre</option>
            </select>
          </label>

          <label class="setting-row">
            <span>Langue</span>
            <select name="language">
              <option value="francais" <?= ($settings['language'] === 'francais') ? 'selected' : '' ?>>Francais</option>
              <option value="anglais" <?= ($settings['language'] === 'anglais') ? 'selected' : '' ?>>Anglais</option>
            </select>
          </label>

          <label class="setting-row">
            <span>Controles parentaux</span>
            <select name="parental_controls">
              <option value="0" <?= ((int) $settings['parental_controls'] === 0) ? 'selected' : '' ?>>Non</option>
              <option value="1" <?= ((int) $settings['parental_controls'] === 1) ? 'selected' : '' ?>>Oui</option>
            </select>
          </label>

          <label class="setting-row">
            <span>Notifications</span>
            <select name="notifications_enabled">
              <option value="1" <?= ((int) $settings['notifications_enabled'] === 1) ? 'selected' : '' ?>>Oui</option>
              <option value="0" <?= ((int) $settings['notifications_enabled'] === 0) ? 'selected' : '' ?>>Non</option>
            </select>
          </label>

          <div class="settings-actions">
            <button type="submit">Enregistrer les preferences</button>
          </div>
        </form>
      </div>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
