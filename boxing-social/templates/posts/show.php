<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Publication</title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/post-show.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <article class="post-card">
      <p class="meta">
        Par
        <a href="/user?username=<?= rawurlencode((string) $post['username']) ?>">
          <?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?>
        </a>
      </p>
      <h1><?= htmlspecialchars((string) ($post['title'] ?: 'Publication sans titre'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="content"><?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?></p>

      <?php if (!empty($post['image_path'])): ?>
        <img class="hero-image" src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Image de publication">
      <?php endif; ?>

      <?php if (!empty($post['location'])): ?>
        <p class="meta">Lieu : <?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <p class="meta">
        Creee le <?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?>
        | Visibilite : <?= htmlspecialchars((string) $post['visibility'], ENT_QUOTES, 'UTF-8') ?>
      </p>

      <div class="actions">
        <p><strong>J'aime :</strong> <?= $likesCount ?></p>
        <?php if ($currentUserId !== null): ?>
          <form method="post" action="/likes/toggle">
            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
            <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
            <button type="submit"><?= $isLiked ? 'Retirer mon j\'aime' : 'J\'aime' ?></button>
          </form>
        <?php endif; ?>
      </div>
    </article>

    <section class="comments-card">
      <h2>Commentaires</h2>

      <?php if (!empty($successComments)): ?>
        <p class="msg-success"><?= htmlspecialchars($successComments, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php foreach ($errorsComments as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <?php foreach ($errorsLikes as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <?php if (empty($comments)): ?>
        <p class="muted">Aucun commentaire pour le moment.</p>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <div class="comment">
            <p>
              <strong>
                <a href="/user?username=<?= rawurlencode((string) $comment['username']) ?>">
                  <?= htmlspecialchars((string) $comment['username'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </strong>
              <?= nl2br(htmlspecialchars((string) $comment['content'], ENT_QUOTES, 'UTF-8')) ?>
            </p>
            <small><?= htmlspecialchars((string) $comment['created_at'], ENT_QUOTES, 'UTF-8') ?></small>

            <?php if ($currentUserId !== null && (int) $currentUserId === (int) $comment['user_id']): ?>
              <form method="post" action="/comments/delete">
                <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
                <button type="submit">Supprimer mon commentaire</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if ($currentUserId !== null): ?>
        <form class="comment-form" method="post" action="/comments">
          <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
          <input type="hidden" name="redirect_to" value="/post?id=<?= (int) $post['id'] ?>">
          <textarea name="content" rows="4" placeholder="Ajouter un commentaire..." required></textarea>
          <button type="submit">Commenter</button>
        </form>
      <?php endif; ?>
    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
