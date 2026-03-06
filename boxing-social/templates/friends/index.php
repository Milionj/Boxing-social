<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Amis</title>
  <link rel="stylesheet" href="/css/friends-index.css">
</head>
<body>
  <main class="page">
    <h1>Gestion des amis</h1>

    <p>
      <a href="/">Accueil</a> |
      <a href="/posts">Posts</a> |
      <a href="/profile">Profil</a>
    </p>

    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <hr>
    <h2>Envoyer une demande</h2>
    <form method="post" action="/friends/send">
      <input type="number" name="user_id" min="1" placeholder="ID utilisateur cible" required>
      <button type="submit">Envoyer</button>
    </form>

    <hr>
    <h2>Demandes recues</h2>
    <?php if (empty($incoming)): ?>
      <p>Aucune demande recue.</p>
    <?php else: ?>
      <?php foreach ($incoming as $req): ?>
        <div class="card">
          <p><strong><?= htmlspecialchars((string) $req['requester_username'], ENT_QUOTES, 'UTF-8') ?></strong> veut etre ton ami.</p>
          <form class="inline" method="post" action="/friends/accept">
            <input type="hidden" name="friendship_id" value="<?= (int) $req['id'] ?>">
            <button type="submit">Accepter</button>
          </form>
          <form class="inline" method="post" action="/friends/decline">
            <input type="hidden" name="friendship_id" value="<?= (int) $req['id'] ?>">
            <button type="submit">Refuser</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <hr>
    <h2>Demandes envoyees</h2>
    <?php if (empty($outgoing)): ?>
      <p>Aucune demande envoyee.</p>
    <?php else: ?>
      <?php foreach ($outgoing as $req): ?>
        <p>En attente: <?= htmlspecialchars((string) $req['addressee_username'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <hr>
    <h2>Mes amis</h2>
    <?php if (empty($friends)): ?>
      <p>Tu n as pas encore d amis.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($friends as $friend): ?>
          <li><?= htmlspecialchars((string) $friend['username'], ENT_QUOTES, 'UTF-8') ?> (ID <?= (int) $friend['id'] ?>)</li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
</body>
</html>
