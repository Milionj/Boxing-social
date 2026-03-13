<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('friends_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css">
  <link rel="stylesheet" href="/css/friends-index.css">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="page app-main">
    <h1><?= htmlspecialchars($t->text('friends_heading'), ENT_QUOTES, 'UTF-8') ?></h1>

    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <hr>
    <h2><?= htmlspecialchars($t->text('friends_send_request'), ENT_QUOTES, 'UTF-8') ?></h2>
    <form method="post" action="/friends/send">
      <input type="text" name="username" placeholder="<?= htmlspecialchars($t->text('friends_username_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required>
      <button type="submit"><?= htmlspecialchars($t->text('friends_send'), ENT_QUOTES, 'UTF-8') ?></button>
    </form>

    <hr>
    <h2><?= htmlspecialchars($t->text('friends_incoming'), ENT_QUOTES, 'UTF-8') ?></h2>
    <?php if (empty($incoming)): ?>
      <p><?= htmlspecialchars($t->text('friends_incoming_empty'), ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
      <?php foreach ($incoming as $req): ?>
        <div class="card">
          <p>
            <strong>
              <a href="/user?username=<?= rawurlencode((string) $req['requester_username']) ?>">
                <?= htmlspecialchars((string) $req['requester_username'], ENT_QUOTES, 'UTF-8') ?>
              </a>
            </strong>
            <?= htmlspecialchars($t->text('friends_wants_friend'), ENT_QUOTES, 'UTF-8') ?>
          </p>
          <form class="inline" method="post" action="/friends/accept">
            <input type="hidden" name="friendship_id" value="<?= (int) $req['id'] ?>">
            <button type="submit"><?= htmlspecialchars($t->text('friends_accept'), ENT_QUOTES, 'UTF-8') ?></button>
          </form>
          <form class="inline" method="post" action="/friends/decline">
            <input type="hidden" name="friendship_id" value="<?= (int) $req['id'] ?>">
            <button type="submit"><?= htmlspecialchars($t->text('friends_decline'), ENT_QUOTES, 'UTF-8') ?></button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <hr>
    <h2><?= htmlspecialchars($t->text('friends_outgoing'), ENT_QUOTES, 'UTF-8') ?></h2>
    <?php if (empty($outgoing)): ?>
      <p><?= htmlspecialchars($t->text('friends_outgoing_empty'), ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
      <?php foreach ($outgoing as $req): ?>
        <p>
          <?= htmlspecialchars($t->text('friends_pending'), ENT_QUOTES, 'UTF-8') ?>:
          <a href="/user?username=<?= rawurlencode((string) $req['addressee_username']) ?>">
            <?= htmlspecialchars((string) $req['addressee_username'], ENT_QUOTES, 'UTF-8') ?>
          </a>
        </p>
      <?php endforeach; ?>
    <?php endif; ?>

    <hr>
    <h2><?= htmlspecialchars($t->text('friends_my_friends'), ENT_QUOTES, 'UTF-8') ?></h2>
    <?php if (empty($friends)): ?>
      <p><?= htmlspecialchars($t->text('friends_empty'), ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
      <ul>
        <?php foreach ($friends as $friend): ?>
          <li>
            <a href="/user?username=<?= rawurlencode((string) $friend['username']) ?>">
              <?= htmlspecialchars((string) $friend['username'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
