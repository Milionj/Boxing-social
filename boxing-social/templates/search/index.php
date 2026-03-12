<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Recherche</title>
  <link rel="stylesheet" href="/css/search-index.css">
  <script src="/js/search-autocomplete.js" defer></script>
</head>
<body>
  <main class="page">
    <header class="hero">
      <p class="eyebrow">Recherche</p>
      <h1>Trouver une personne ou une publication</h1>
      <p class="intro">Recherche rapide par pseudo, titre ou contenu.</p>

      <form class="search-form" method="get" action="/search" autocomplete="off">
        <div class="autocomplete">
          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Exemple : serge, boxe, sparring..."
            data-user-autocomplete
            data-autocomplete-endpoint="/search/usernames"
            required
          >
          <div class="autocomplete-list" hidden></div>
        </div>
        <button type="submit">Rechercher</button>
      </form>

      <nav class="links">
        <a href="/">Accueil</a>
        <a href="/posts">Publications</a>
        <a href="/friends">Amis</a>
        <a href="/messages">Messages</a>
      </nav>
    </header>

    <?php if ($query === ''): ?>
      <section class="empty-state">
        <h2>Lance une recherche</h2>
        <p>Saisis un mot-cle pour afficher les utilisateurs et les publications correspondants.</p>
      </section>
    <?php else: ?>
      <section class="results-grid">
        <section class="panel">
          <div class="panel-head">
            <h2>Utilisateurs</h2>
            <span><?= count($users) ?> resultat(s)</span>
          </div>

          <?php if (empty($users)): ?>
            <p class="muted">Aucun utilisateur ne correspond a cette recherche.</p>
          <?php else: ?>
            <div class="cards">
              <?php foreach ($users as $user): ?>
                <article class="card">
                  <h3>
                    <a class="user-link" href="/user?username=<?= rawurlencode((string) $user['username']) ?>">
                      <?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </h3>
                  <p>
                    <?= htmlspecialchars((string) ($user['bio'] ?? 'Aucune bio pour le moment.'), ENT_QUOTES, 'UTF-8') ?>
                  </p>
                  <form method="post" action="/friends/send">
                    <input type="hidden" name="username" value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Envoyer une demande</button>
                  </form>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>

        <section class="panel">
          <div class="panel-head">
            <h2>Publications</h2>
            <span><?= count($posts) ?> resultat(s)</span>
          </div>

          <?php if (empty($posts)): ?>
            <p class="muted">Aucune publication ne correspond a cette recherche.</p>
          <?php else: ?>
            <div class="cards">
              <?php foreach ($posts as $post): ?>
                <article class="card post-card">
                  <p class="post-meta">
                    Par
                    <a class="user-link" href="/user?username=<?= rawurlencode((string) $post['username']) ?>">
                      <?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </p>
                  <h3><?= htmlspecialchars((string) ($post['title'] ?: 'Publication sans titre'), ENT_QUOTES, 'UTF-8') ?></h3>
                  <?php $excerpt = substr((string) $post['content'], 0, 180); ?>
                  <p><?= htmlspecialchars($excerpt . (strlen((string) $post['content']) > 180 ? '...' : ''), ENT_QUOTES, 'UTF-8') ?></p>
                  <?php if (!empty($post['image_path'])): ?>
                    <img src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Image de publication">
                  <?php endif; ?>
                  <a class="btn-link" href="/posts">Voir le fil</a>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
