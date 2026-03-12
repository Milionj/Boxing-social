<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Contact</title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/static-page.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/templates/partials/app-navbar.php'; ?>
  <main class="static-page app-main">
    <section class="static-hero">
      <p class="static-eyebrow">Contact</p>
      <h1>Entrer en contact avec Boxing Social</h1>
      <p>Une question, un retour produit ou un souci sur la plateforme. Utilise ce formulaire de contact.</p>
    </section>

    <section class="static-card">
      <form class="contact-form" method="post" action="#">
        <label>
          Email
          <input type="email" placeholder="ton@email.com" disabled>
        </label>

        <label>
          Sujet
          <select disabled>
            <option>Question generale</option>
            <option>Support technique</option>
            <option>Signalement</option>
          </select>
        </label>

        <label>
          Message
          <textarea rows="7" placeholder="Explique ton besoin..." disabled></textarea>
        </label>

        <button type="button" disabled>Envoyer</button>
      </form>
      <p class="static-note">Formulaire present pour la structure. L'envoi sera branche quand on traitera les pages restantes.</p>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
