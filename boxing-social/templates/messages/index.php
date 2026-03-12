<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Messagerie</title>
  <link rel="stylesheet" href="/css/messages-index.css">
</head>
<body>
  <main class="page">
    <h1>Messagerie</h1>

    <p>
      <a href="/">Accueil</a> |
      <a href="/notifications">Notifications</a> |
      <a href="/friends">Amis</a>
    </p>

    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <div class="wrap">
      <section class="col left">
        <h2>Conversations</h2>

        <form method="get" action="/messages">
          <input type="text" name="username" placeholder="Pseudo de la personne" required>
          <button type="submit">Ouvrir</button>
        </form>

        <?php if (empty($conversations)): ?>
          <p>Aucune conversation.</p>
        <?php else: ?>
          <ul>
            <?php foreach ($conversations as $conv): ?>
              <li>
                <a href="/messages?user_id=<?= (int) $conv['other_user_id'] ?>">
                  <?= htmlspecialchars((string) $conv['username'], ENT_QUOTES, 'UTF-8') ?>
                </a>
                -
                <a href="/user?username=<?= rawurlencode((string) $conv['username']) ?>">voir le profil</a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="col right">
        <h2>Discussion</h2>

        <?php if ($selectedUserId <= 0): ?>
          <p>Choisis une conversation.</p>

          <hr>
          <h3>Nouveau message</h3>
          <form method="post" action="/messages/send">
            <input type="text" name="receiver_username" placeholder="Pseudo du destinataire" required>
            <br><br>
            <textarea name="content" rows="3" cols="60" placeholder="Ecrire un premier message..." required></textarea>
            <br>
            <button type="submit">Envoyer</button>
          </form>
        <?php else: ?>
          <?php if (empty($thread)): ?>
            <p>Aucun message.</p>
          <?php else: ?>
            <?php foreach ($thread as $m): ?>
              <div class="msg">
                <strong><?= ((int) $m['sender_id'] === (int) ($_SESSION['user']['id'] ?? 0)) ? 'Moi' : 'La personne' ?>:</strong>
                <?= nl2br(htmlspecialchars((string) $m['content'], ENT_QUOTES, 'UTF-8')) ?>
                <br>
                <small><?= htmlspecialchars((string) $m['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <hr>
          <form method="post" action="/messages/send">
            <input type="hidden" name="receiver_id" value="<?= (int) $selectedUserId ?>">
            <textarea name="content" rows="3" cols="60" placeholder="Ecrire un message..." required></textarea>
            <br>
            <button type="submit">Envoyer</button>
          </form>
        <?php endif; ?>
      </section>
    </div>
  </main>
</body>
</html>
