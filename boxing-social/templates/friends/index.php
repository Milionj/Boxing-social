<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('friends_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260315o">
  <link rel="stylesheet" href="/css/friends-index.css?v=20260317a">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="friends-page app-main" data-friends-page>
    <section class="friends-hero">
      <h1><?= htmlspecialchars($t->text('friends_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($t->text('friends_intro'), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <section class="friends-stats">
      <article class="friends-stat-card">
        <span><?= htmlspecialchars($t->text('friends_stats_incoming'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong data-friends-count="incoming"><?= count($incoming) ?></strong>
      </article>
      <article class="friends-stat-card">
        <span><?= htmlspecialchars($t->text('friends_stats_outgoing'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong data-friends-count="outgoing"><?= count($outgoing) ?></strong>
      </article>
      <article class="friends-stat-card">
        <span><?= htmlspecialchars($t->text('friends_stats_friends'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong data-friends-count="friends"><?= count($friends) ?></strong>
      </article>
    </section>

    <div class="friends-layout">
      <aside class="friends-request-card card" data-social-scope>
        <p class="friends-card__eyebrow"><?= htmlspecialchars($t->text('friends_request_card'), ENT_QUOTES, 'UTF-8') ?></p>
        <h2><?= htmlspecialchars($t->text('friends_send_request'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t->text('friends_request_help'), ENT_QUOTES, 'UTF-8') ?></p>

        <form class="friends-request-form" method="post" action="/friends/send" data-friend-send-form>
          <label>
            <span><?= htmlspecialchars($t->text('friends_username_placeholder'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="text" name="username" placeholder="<?= htmlspecialchars($t->text('friends_username_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required>
          </label>
          <button
            type="submit"
            data-friend-send-button
            data-label-default="<?= htmlspecialchars($t->text('friends_send'), ENT_QUOTES, 'UTF-8') ?>"
            data-label-sent="<?= htmlspecialchars($t->text('friends_request_sent'), ENT_QUOTES, 'UTF-8') ?>"
          ><?= htmlspecialchars($t->text('friends_send'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
        <p class="interaction-feedback" data-interaction-feedback hidden></p>
      </aside>

      <div class="friends-stack">
        <section class="friends-card card">
          <div class="friends-card__head">
            <div>
              <h2><?= htmlspecialchars($t->text('friends_incoming'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <span class="friends-card__count" data-friends-count="incoming"><?= count($incoming) ?></span>
          </div>

          <p class="friends-empty" data-friends-empty="incoming" <?= empty($incoming) ? '' : 'hidden' ?>><?= htmlspecialchars($t->text('friends_incoming_empty'), ENT_QUOTES, 'UTF-8') ?></p>
          <?php if (!empty($incoming)): ?>
            <div class="friends-list" data-friends-incoming-list>
              <?php foreach ($incoming as $req): ?>
                <article
                  class="friend-row"
                  data-social-scope
                  data-friendship-row
                  data-friendship-id="<?= (int) $req['id'] ?>"
                  data-friend-username="<?= htmlspecialchars((string) $req['requester_username'], ENT_QUOTES, 'UTF-8') ?>"
                  data-friend-url="/user?username=<?= rawurlencode((string) $req['requester_username']) ?>"
                >
                  <div class="friend-row__identity">
                    <div class="friend-row__avatar"><?= htmlspecialchars(strtoupper(substr((string) $req['requester_username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                    <div>
                      <h3>
                        <a href="/user?username=<?= rawurlencode((string) $req['requester_username']) ?>">
                          <?= htmlspecialchars((string) $req['requester_username'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                      </h3>
                      <p><?= htmlspecialchars($t->text('friends_wants_friend'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                  </div>
                  <div class="friend-row__actions">
                    <form method="post" action="/friends/accept" data-friend-accept-form>
                      <input type="hidden" name="friendship_id" value="<?= (int) $req['id'] ?>">
                      <button type="submit"><?= htmlspecialchars($t->text('friends_accept'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                    <form method="post" action="/friends/decline" data-friend-decline-form>
                      <input type="hidden" name="friendship_id" value="<?= (int) $req['id'] ?>">
                      <button class="button-secondary" type="submit"><?= htmlspecialchars($t->text('friends_decline'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                  </div>
                  <p class="interaction-feedback" data-interaction-feedback hidden></p>
                </article>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="friends-list" data-friends-incoming-list hidden></div>
          <?php endif; ?>
        </section>

        <section class="friends-card card">
          <div class="friends-card__head">
            <div>
              <h2><?= htmlspecialchars($t->text('friends_outgoing'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <span class="friends-card__count" data-friends-count="outgoing"><?= count($outgoing) ?></span>
          </div>

          <p class="friends-empty" data-friends-empty="outgoing" <?= empty($outgoing) ? '' : 'hidden' ?>><?= htmlspecialchars($t->text('friends_outgoing_empty'), ENT_QUOTES, 'UTF-8') ?></p>
          <?php if (!empty($outgoing)): ?>
            <div class="friends-list" data-friends-outgoing-list>
              <?php foreach ($outgoing as $req): ?>
                <article
                  class="friend-row friend-row--simple"
                  data-social-scope
                  data-friendship-row
                  data-friendship-id="<?= (int) $req['id'] ?>"
                >
                  <div class="friend-row__identity">
                    <div class="friend-row__avatar"><?= htmlspecialchars(strtoupper(substr((string) $req['addressee_username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                    <div>
                      <h3>
                        <a href="/user?username=<?= rawurlencode((string) $req['addressee_username']) ?>">
                          <?= htmlspecialchars((string) $req['addressee_username'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                      </h3>
                      <p><?= htmlspecialchars($t->text('friends_pending_with'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                  </div>
                  <div class="friend-row__actions">
                    <a class="friend-row__profile-link" href="/user?username=<?= rawurlencode((string) $req['addressee_username']) ?>">
                      <?= htmlspecialchars($t->text('friends_open_profile'), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <form method="post" action="/friends/cancel" data-friend-cancel-form>
                      <input type="hidden" name="friendship_id" value="<?= (int) $req['id'] ?>">
                      <button class="button-secondary" type="submit"><?= htmlspecialchars($t->text('friends_cancel'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                  </div>
                  <p class="interaction-feedback" data-interaction-feedback hidden></p>
                </article>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="friends-list" data-friends-outgoing-list hidden></div>
          <?php endif; ?>
        </section>

        <section class="friends-card card">
          <div class="friends-card__head">
            <div>
              <h2><?= htmlspecialchars($t->text('friends_my_friends'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <span class="friends-card__count" data-friends-count="friends"><?= count($friends) ?></span>
          </div>

          <p class="friends-empty" data-friends-empty="friends" <?= empty($friends) ? '' : 'hidden' ?>><?= htmlspecialchars($t->text('friends_empty'), ENT_QUOTES, 'UTF-8') ?></p>
          <?php if (!empty($friends)): ?>
            <div class="friends-grid" data-friends-grid>
              <?php foreach ($friends as $friend): ?>
                <article class="friend-tile" data-social-scope data-friendship-row data-friendship-id="<?= (int) ($friend['friendship_id'] ?? 0) ?>">
                  <div class="friend-tile__avatar"><?= htmlspecialchars(strtoupper(substr((string) $friend['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                  <h3>
                    <a href="/user?username=<?= rawurlencode((string) $friend['username']) ?>">
                      <?= htmlspecialchars((string) $friend['username'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </h3>
                  <div class="friend-tile__actions">
                    <a class="friend-tile__link" href="/user?username=<?= rawurlencode((string) $friend['username']) ?>">
                      <?= htmlspecialchars($t->text('friends_open_profile'), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <form method="post" action="/friends/remove" data-friend-remove-form>
                      <input type="hidden" name="friendship_id" value="<?= (int) ($friend['friendship_id'] ?? 0) ?>">
                      <button class="button-secondary" type="submit"><?= htmlspecialchars($t->text('friends_remove'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                  </div>
                  <p class="interaction-feedback" data-interaction-feedback hidden></p>
                </article>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="friends-grid" data-friends-grid hidden></div>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
