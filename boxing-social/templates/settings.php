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
      <p>Page de parametres generale, distincte du profil. Les controles seront rendus persistants ensuite.</p>
    </section>

    <section class="static-card settings-grid">
      <div class="settings-menu">
        <button type="button" class="is-active">General</button>
      </div>

      <div class="settings-list">
        <div class="setting-row">
          <span>Theme</span>
          <span>Systeme</span>
        </div>
        <div class="setting-row">
          <span>Langue</span>
          <span>Francais</span>
        </div>
        <div class="setting-row">
          <span>Controles parentaux</span>
          <span>Non</span>
        </div>
        <div class="setting-row">
          <span>Notifications</span>
          <span>Oui</span>
        </div>
      </div>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
