<?php require dirname(__DIR__) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('settings_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260317p">
  <link rel="stylesheet" href="/css/static-page.css?v=20260315i">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/templates/partials/app-navbar.php'; ?>
  <main class="static-page app-main">
    <section class="static-hero">
      <h1><?= htmlspecialchars($t->text('settings_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
    </section>

    <section class="static-card static-card--settings settings-grid">
      <div class="settings-menu">
        <button type="button" class="is-active"><?= htmlspecialchars($t->text('settings_general'), ENT_QUOTES, 'UTF-8') ?></button>
      </div>

      <div class="settings-content">
        <?php if (!empty($success)): ?>
          <p class="msg-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <?php foreach ($errors as $error): ?>
            <p class="msg-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!$tableReady): ?>
          <p class="static-note"><?= htmlspecialchars($t->text('settings_table_missing'), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="settings-list" method="post" action="/settings">
          <label class="setting-row">
            <span><?= htmlspecialchars($t->text('settings_theme'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="theme">
              <option value="systeme" <?= ($settings['theme'] === 'systeme') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('settings_theme_system'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="clair" <?= ($settings['theme'] === 'clair') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('settings_theme_light'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="sombre" <?= ($settings['theme'] === 'sombre') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('settings_theme_dark'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>

          <label class="setting-row">
            <span><?= htmlspecialchars($t->text('settings_language'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="language">
              <option value="francais" <?= ($settings['language'] === 'francais') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('settings_language_french'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="anglais" <?= ($settings['language'] === 'anglais') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('settings_language_english'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>

          <label class="setting-row">
            <span><?= htmlspecialchars($t->text('settings_parental_controls'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="parental_controls">
              <option value="0" <?= ((int) $settings['parental_controls'] === 0) ? 'selected' : '' ?>><?= htmlspecialchars($t->text('common_no'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="1" <?= ((int) $settings['parental_controls'] === 1) ? 'selected' : '' ?>><?= htmlspecialchars($t->text('common_yes'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>

          <label class="setting-row">
            <span><?= htmlspecialchars($t->text('settings_notifications'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="notifications_enabled">
              <option value="1" <?= ((int) $settings['notifications_enabled'] === 1) ? 'selected' : '' ?>><?= htmlspecialchars($t->text('common_yes'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="0" <?= ((int) $settings['notifications_enabled'] === 0) ? 'selected' : '' ?>><?= htmlspecialchars($t->text('common_no'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>

          <div class="settings-actions">
            <button type="submit"><?= htmlspecialchars($t->text('settings_save'), ENT_QUOTES, 'UTF-8') ?></button>
          </div>
        </form>

      </div>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
