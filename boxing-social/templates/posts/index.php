<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Feed Posts</title>
</head>
<body>
  <h1>Derniers posts</h1>

  <p>
    <a href="/">Accueil</a> |
    <a href="/posts/create">Creer un post</a> |
    <a href="/profile">Mon profil</a>
  </p>

  <?php $errorsComments = $_SESSION['errors_comments'] ?? []; ?>
  <?php $successComments = $_SESSION['success_comments'] ?? ''; ?>
  <?php unset($_SESSION['errors_comments'], $_SESSION['success_comments']); ?>

  <?php if (!empty($successComments)): ?>
    <p style="color:#067647;"><?= htmlspecialchars($successComments, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <?php if (!empty($errorsComments)): ?>
    <?php foreach ($errorsComments as $error): ?>
      <p style="color:#b42318;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (empty($feed)): ?>
    <p>Aucun post pour le moment.</p>
  <?php else: ?>
    <?php foreach ($feed as $post): ?>
      <article style="border:1px solid #ddd;padding:12px;margin:12px 0;">
        <h3><?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
        <p><strong>Auteur:</strong> <?= htmlspecialchars((string) $post['username'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><?= nl2br(htmlspecialchars((string) $post['content'], ENT_QUOTES, 'UTF-8')) ?></p>

        <?php if (!empty($post['image_path'])): ?>
          <p><img src="<?= htmlspecialchars((string) $post['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Image post" style="max-width:280px;height:auto;"></p>
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
        <h4>Commentaires</h4>

        <?php $postComments = $commentsByPost[(int) $post['id']] ?? []; ?>
        <?php if (empty($postComments)): ?>
          <p>Aucun commentaire.</p>
        <?php else: ?>
          <?php foreach ($postComments as $comment): ?>
            <div style="padding:8px;border:1px solid #eee;margin-bottom:8px;">
              <p>
                <strong><?= htmlspecialchars((string) $comment['username'], ENT_QUOTES, 'UTF-8') ?>:</strong>
                <?= nl2br(htmlspecialchars((string) $comment['content'], ENT_QUOTES, 'UTF-8')) ?>
              </p>
              <small><?= htmlspecialchars((string) $comment['created_at'], ENT_QUOTES, 'UTF-8') ?></small>

              <?php if ($currentUserId !== null && (int) $currentUserId === (int) $comment['user_id']): ?>
                <form method="post" action="/comments/delete" style="margin-top:6px;">
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
</body>
</html>
