<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('messages_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260315i">
  <link rel="stylesheet" href="/css/messages-index.css?v=20260315i">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="messages-page app-main">
    <section class="messages-hero">
      <h1><?= htmlspecialchars($t->text('messages_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('messages_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <div class="messages-layout">
      <aside class="messages-sidebar card">
        <div class="messages-card__head">
          <div>
            <h2><?= htmlspecialchars($t->text('messages_conversations'), ENT_QUOTES, 'UTF-8') ?></h2>
          </div>
        </div>

        <form class="messages-open-form" method="get" action="/messages">
          <label>
            <span><?= htmlspecialchars($t->text('messages_recipient'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="text" name="username" placeholder="<?= htmlspecialchars($t->text('messages_recipient_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required>
          </label>
          <button type="submit"><?= htmlspecialchars($t->text('messages_open'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
        <p class="messages-help"><?= htmlspecialchars($t->text('messages_open_help'), ENT_QUOTES, 'UTF-8') ?></p>

        <?php if (empty($conversations)): ?>
          <p class="messages-empty"><?= htmlspecialchars($t->text('messages_no_conversations'), ENT_QUOTES, 'UTF-8') ?></p>
        <?php else: ?>
          <div class="conversation-list">
            <?php foreach ($conversations as $conv): ?>
              <?php $isActive = ((int) $conv['other_user_id'] === (int) $selectedUserId); ?>
              <article class="conversation-item<?= $isActive ? ' is-active' : '' ?>">
                <a class="conversation-item__main" href="/messages?user_id=<?= (int) $conv['other_user_id'] ?>">
                  <span class="conversation-item__avatar"><?= htmlspecialchars(strtoupper(substr((string) $conv['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="conversation-item__copy">
                    <strong><?= htmlspecialchars((string) $conv['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars((string) $conv['last_message_at'], ENT_QUOTES, 'UTF-8') ?></small>
                  </span>
                </a>
                <a class="conversation-item__profile" href="/user?username=<?= rawurlencode((string) $conv['username']) ?>">
                  <?= htmlspecialchars($t->text('messages_profile_link'), ENT_QUOTES, 'UTF-8') ?>
                </a>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </aside>

      <section class="messages-thread card">
        <div class="messages-card__head">
          <div>
            <h2><?= htmlspecialchars($t->text('messages_discussion'), ENT_QUOTES, 'UTF-8') ?></h2>
          </div>
        </div>

        <?php if ($selectedUserId <= 0): ?>
          <p class="messages-empty"><?= htmlspecialchars($t->text('messages_choose'), ENT_QUOTES, 'UTF-8') ?></p>

          <div class="messages-new-card">
            <h3><?= htmlspecialchars($t->text('messages_new_message'), ENT_QUOTES, 'UTF-8') ?></h3>
            <form class="messages-send-form" method="post" action="/messages/send">
              <label>
                <span><?= htmlspecialchars($t->text('messages_recipient'), ENT_QUOTES, 'UTF-8') ?></span>
                <input type="text" name="receiver_username" placeholder="<?= htmlspecialchars($t->text('messages_recipient_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required>
              </label>
              <label>
                <span><?= htmlspecialchars($t->text('messages_message_placeholder'), ENT_QUOTES, 'UTF-8') ?></span>
                <textarea name="content" rows="4" placeholder="<?= htmlspecialchars($t->text('messages_first_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
              </label>
              <button type="submit"><?= htmlspecialchars($t->text('messages_send'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
          </div>
        <?php else: ?>
          <?php if (empty($thread)): ?>
            <p class="messages-empty"><?= htmlspecialchars($t->text('messages_no_messages'), ENT_QUOTES, 'UTF-8') ?></p>
          <?php else: ?>
            <div class="message-list">
              <?php foreach ($thread as $m): ?>
                <?php $isMine = ((int) $m['sender_id'] === (int) ($_SESSION['user']['id'] ?? 0)); ?>
                <article class="message-bubble<?= $isMine ? ' is-mine' : '' ?>">
                  <div class="message-bubble__head">
                    <strong><?= htmlspecialchars($isMine ? $t->text('messages_you') : $t->text('messages_other'), ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars((string) $m['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
                  </div>
                  <p><?= nl2br(htmlspecialchars((string) $m['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form class="messages-send-form messages-send-form--reply" method="post" action="/messages/send">
            <input type="hidden" name="receiver_id" value="<?= (int) $selectedUserId ?>">
            <label>
              <span><?= htmlspecialchars($t->text('messages_message_placeholder'), ENT_QUOTES, 'UTF-8') ?></span>
              <textarea name="content" rows="4" placeholder="<?= htmlspecialchars($t->text('messages_message_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
            </label>
            <button type="submit"><?= htmlspecialchars($t->text('messages_send'), ENT_QUOTES, 'UTF-8') ?></button>
          </form>
        <?php endif; ?>
      </section>
    </div>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
