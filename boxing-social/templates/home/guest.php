<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Boxing Social</title>
  <link rel="stylesheet" href="/css/guest-home.css?v=20260315i">
  <link rel="stylesheet" href="/css/scroll-top.css?v=20260315i">
  <script defer src="/js/guest-hero.js?v=20260314a"></script>
</head>
<body class="guest-page">
  <main class="guest-shell">
    <section class="guest-hero">
      <div class="guest-hero__content">
        <a class="guest-brand" href="/">
          <img src="/img/Bonlogo.png" alt="Logo Boxing Social">
          <span>
            <strong>Boxing Social</strong>
            <small>Communauté boxe</small>
          </span>
        </a>

        <div class="guest-copy">
          <p class="guest-kicker">Réseau social boxe</p>
          <h1 class="guest-title" data-hero-title>
            <span class="guest-title__line">Trouve des partenaires.</span>
            <span class="guest-title__line">Publie.</span>
            <span class="guest-title__line">Organise tes séances.</span>
          </h1>
          <p class="guest-intro">
            Boxing Social centralise les publications, les demandes d’amis, la messagerie
            et les déclarations de séances d’entraînement dans une interface simple.
          </p>
        </div>

        <div class="guest-actions">
          <a class="guest-btn guest-btn--primary" href="/register">Inscription</a>
          <a class="guest-btn guest-btn--secondary" href="/login">Connexion</a>
        </div>

        <div class="guest-legal-links">
          <a href="/contact">Contact</a>
          <a href="/privacy">Politique de confidentialité</a>
        </div>
      </div>

      <div class="guest-hero__visual" aria-hidden="true">
        <div class="guest-preview-card guest-preview-card--primary">
          <span class="guest-preview-card__label">Séances</span>
          <strong>Partenaires</strong>
          <p>Retrouve rapidement les profils et les publications qui correspondent à ta pratique.</p>
        </div>

        <div class="guest-preview-grid">
          <article class="guest-preview-card">
            <span class="guest-preview-card__value">24</span>
            <p>discussions actives</p>
          </article>

          <article class="guest-preview-card">
            <span class="guest-preview-card__value">08</span>
            <p>séances à venir</p>
          </article>
        </div>

        <div class="guest-preview-strip">
          <span>Publier</span>
          <span>Échanger</span>
          <span>Organiser</span>
        </div>
      </div>
    </section>

    <section class="guest-grid" aria-label="Fonctionnalites principales">
      <article class="guest-card">
        <h2>Publier facilement</h2>
        <p>
          Partage une publication simple ou annonce une séance avec un lieu,
          une date et un appel à intérêt.
        </p>
      </article>

      <article class="guest-card">
        <h2>Échanger avec la communauté</h2>
        <p>
          Ajoute des amis, reçois des notifications utiles et discute en privé
          avec les membres qui partagent ta pratique.
        </p>
      </article>

      <article class="guest-card">
        <h2>Suivre les opportunités</h2>
        <p>
          Recherche un pseudo, consulte les profils publics et retrouve rapidement
          les publications ou séances qui t’intéressent.
        </p>
      </article>
    </section>

    <section class="guest-panel">
      <div class="guest-panel__copy">
        <h2>Deux accès clairs selon ton besoin</h2>
        <p>
          Inscris-toi pour rejoindre la plateforme, ou reconnecte-toi directement
          si ton compte existe déjà.
        </p>
      </div>

      <div class="guest-services">
        <article class="guest-service">
          <h3>Inscription</h3>
          <p>Crée ton compte, complète ton profil et commence à publier.</p>
          <a href="/register">Ouvrir l’inscription</a>
        </article>

        <article class="guest-service">
          <h3>Connexion</h3>
          <p>Retrouve ton espace, tes messages, tes amis et ton fil personnel.</p>
          <a href="/login">Ouvrir la connexion</a>
        </article>
      </div>
    </section>
  </main>
  <?php require dirname(__DIR__) . '/partials/cookie-notice.php'; ?>
  <?php require dirname(__DIR__) . '/partials/scroll-top.php'; ?>
</body>
</html>
