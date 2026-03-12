<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Fil des publications</title>
  <link rel="stylesheet" href="/css/posts-index.css">
</head>
<body>
  <main class="page">
    <h1>Dernieres publications</h1>

    <p>
      <a href="/">Accueil</a> |
      <a href="/posts/create">Creer une publication</a> |
      <a href="/profile">Mon profil</a>
    </p>

    <?php $errorsComments = $_SESSION['errors_comments'] ?? []; ?>
    <?php $successComments = $_SESSION['success_comments'] ?? ''; ?>
    <?php $errorsLikes = $_SESSION['errors_likes'] ?? []; ?>
    <?php unset($_SESSION['errors_comments'], $_SESSION['success_comments'], $_SESSION['errors_likes']); ?>

    <?php if (!empty($successComments)): ?>
      <p class="msg-success"><?= htmlspecialchars($successComments, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errorsComments)): ?>
      <?php foreach ($errorsComments as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($errorsLikes)): ?>
      <?php foreach ($errorsLikes as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (empty($feed)): ?>
      <p>Aucun post pour le moment.</p>
    <?php else: ?>
      <?php foreach ($feed as $post): ?>
        <article class="post">
          <h3><?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><strong>Auteur:</strong> <?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?></p>

          <?php if (!empty($post['image_path'])): ?>
            <p><img class="post-image" src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Image de la publication"></p>
          <?php endif; ?>

          <?php if (!empty($post['location'])): ?>
            <p><strong>Lieu:</strong> <?= htmlspecialchars((string) $post['location'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>

          <?php $currentUserId = $_SESSION['user']['id'] ?? null; ?>
          <?php if ($currentUserId !== null && (int) $currentUserId === (int) $post['user_id']): ?>
            <p><a href="/posts/edit?id=<?= (int) $post['id'] ?>">Modifier</a></p>
            <form method="post" action="/posts/delete" onsubmit="return confirm('Supprimer ce post ?');">
              <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
              <button type="submit">Supprimer</button>
            </form>
          <?php endif; ?>

          <p><small><?= htmlspecialchars((string) $post['created_at'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string) $post['visibility'], ENT_QUOTES, 'UTF-8') ?></small></p>

          <hr>
          <?php $postId = (int) $post['id']; ?>
          <?php $likesCount = (int) ($likesCountByPost[$postId] ?? 0); ?>
          <?php $isLiked = (bool) ($likedByCurrentUser[$postId] ?? false); ?>

          <p><strong>J'aime:</strong> <?= $likesCount ?></p>

          <?php if ($currentUserId !== null): ?>
            <form method="post" action="/likes/toggle">
              <input type="hidden" name="post_id" value="<?= $postId ?>">
              <button type="submit"><?= $isLiked ? 'Retirer mon j\'aime' : 'J\'aime' ?></button>
            </form>
          <?php endif; ?>

          <hr>
          <h4>Commentaires</h4>

          <?php $postComments = $commentsByPost[(int) $post['id']] ?? []; ?>
          <?php if (empty($postComments)): ?>
            <p>Aucun commentaire.</p>
          <?php else: ?>
            <?php foreach ($postComments as $comment): ?>
              <div class="comment">
                <p>
                  <strong><?= htmlspecialchars((string) $comment['username'], ENT_QUOTES, 'UTF-8') ?>:</strong>
                  <?= nl2br(htmlspecialchars((string) $comment['content'], ENT_QUOTES, 'UTF-8')) ?>
                </p>
                <small><?= htmlspecialchars((string) $comment['created_at'], ENT_QUOTES, 'UTF-8') ?></small>

                <?php if ($currentUserId !== null && (int) $currentUserId === (int) $comment['user_id']): ?>
                  <form class="form-inline" method="post" action="/comments/delete">
                    <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                    <button type="submit">Supprimer commentaire</button>
                  </form>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php if ($currentUserId !== null): ?>
            <form method="post" action="/comments">
              <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
              <textarea name="content" rows="2" cols="50" placeholder="Ajouter un commentaire..." required></textarea>
              <br>
              <button type="submit">Commenter</button>
            </form>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</body>
</html>
