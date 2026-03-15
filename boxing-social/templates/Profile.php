<?php require dirname(__DIR__) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('profile_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260315i">
  <link rel="stylesheet" href="/css/profile.css?v=20260315i">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__) . '/templates/partials/app-navbar.php'; ?>
  <main class="profile-page app-main">
    <?php $errorsPassword = $_SESSION['errors_password'] ?? []; ?>
    <?php $successPassword = $_SESSION['success_password'] ?? ''; ?>
    <?php unset($_SESSION['errors_password'], $_SESSION['success_password']); ?>

    <?php $errorsAvatar = $_SESSION['errors_avatar'] ?? []; ?>
    <?php $successAvatar = $_SESSION['success_avatar'] ?? ''; ?>
    <?php unset($_SESSION['errors_avatar'], $_SESSION['success_avatar']); ?>

    <section class="profile-hero">
      <h1><?= htmlspecialchars($t->text('profile_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
    </section>

    <div class="profile-layout">
      <aside class="profile-summary card">
        <div class="profile-summary__avatar-wrap">
          <?php if (!empty($user['avatar_path'])): ?>
            <img class="profile-summary__avatar" src="<?= htmlspecialchars((string) $user['avatar_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->text('profile_avatar_alt'), ENT_QUOTES, 'UTF-8') ?>">
          <?php else: ?>
            <div class="profile-summary__avatar profile-summary__avatar--fallback">
              <?= htmlspecialchars(strtoupper(substr((string) $user['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="profile-summary__identity">
          <p class="profile-summary__eyebrow"><?= htmlspecialchars($t->text('profile_identity_heading'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="profile-summary__meta"><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></p>
          <p class="profile-summary__meta"><?= htmlspecialchars($t->text('profile_member_since'), ENT_QUOTES, 'UTF-8') ?> : <?= htmlspecialchars((string) $user['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="profile-summary__bio-block">
          <h3><?= htmlspecialchars($t->text('profile_bio'), ENT_QUOTES, 'UTF-8') ?></h3>
          <p>
            <?= nl2br(htmlspecialchars((string) (($user['bio'] ?? '') !== '' ? $user['bio'] : $t->text('profile_bio_empty')), ENT_QUOTES, 'UTF-8')) ?>
          </p>
        </div>
      </aside>

      <div class="profile-stack">
        <section class="card profile-card">
          <div class="profile-card__head">
            <div>
              <h2><?= htmlspecialchars($t->text('profile_account_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
          </div>

          <?php if (!empty($success)): ?>
            <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
              <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
          <?php endif; ?>

          <form class="profile-form" method="post" action="/profile">
            <label>
              <span><?= htmlspecialchars($t->text('profile_username'), ENT_QUOTES, 'UTF-8') ?></span>
              <input name="username" required value="<?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
              <span><?= htmlspecialchars($t->text('profile_email'), ENT_QUOTES, 'UTF-8') ?></span>
              <input name="email" type="email" required value="<?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label class="profile-form__bio">
              <span><?= htmlspecialchars($t->text('profile_bio'), ENT_QUOTES, 'UTF-8') ?></span>
              <textarea name="bio" rows="7" maxlength="500" placeholder="<?= htmlspecialchars($t->text('profile_bio_placeholder'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($user['bio'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <div class="profile-form__footer">
              <div class="profile-form__meta">
                <span><?= htmlspecialchars($t->text('profile_id'), ENT_QUOTES, 'UTF-8') ?> : <?= (int) $user['id'] ?></span>
              </div>
              <button type="submit"><?= htmlspecialchars($t->text('profile_save'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
          </form>

        </section>

        <section class="card profile-card">
          <div class="profile-card__head">
            <div>
              <h2><?= htmlspecialchars($t->text('profile_avatar_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
          </div>

          <?php if (!empty($successAvatar)): ?>
            <p class="msg-success"><?= htmlspecialchars($successAvatar, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>

          <?php if (!empty($errorsAvatar)): ?>
            <?php foreach ($errorsAvatar as $error): ?>
              <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
          <?php endif; ?>

          <form class="profile-form" method="post" action="/profile/avatar" enctype="multipart/form-data">
            <label>
              <span><?= htmlspecialchars($t->text('profile_avatar_heading'), ENT_QUOTES, 'UTF-8') ?></span>
              <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" required>
            </label>
            <div class="profile-form__footer">
              <small>JPG, PNG ou WEBP, 2MB maximum.</small>
              <button type="submit"><?= htmlspecialchars($t->text('profile_avatar_update'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
          </form>

        </section>

        <section class="card profile-card">
          <div class="profile-card__head">
            <div>
              <h2><?= htmlspecialchars($t->text('profile_password_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
          </div>

          <?php if (!empty($successPassword)): ?>
            <p class="msg-success"><?= htmlspecialchars($successPassword, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>

          <?php if (!empty($errorsPassword)): ?>
            <?php foreach ($errorsPassword as $error): ?>
              <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
          <?php endif; ?>

          <form class="profile-form" method="post" action="/profile/password">
            <label>
              <span><?= htmlspecialchars($t->text('profile_password_current'), ENT_QUOTES, 'UTF-8') ?></span>
              <input type="password" name="current_password" required>
            </label>
            <label>
              <span><?= htmlspecialchars($t->text('profile_password_new'), ENT_QUOTES, 'UTF-8') ?></span>
              <input type="password" name="new_password" required>
            </label>
            <label>
              <span><?= htmlspecialchars($t->text('profile_password_confirm'), ENT_QUOTES, 'UTF-8') ?></span>
              <input type="password" name="confirm_password" required>
            </label>
            <div class="profile-form__footer">
              <small>8 caractères minimum, avec majuscule, minuscule et chiffre.</small>
              <button type="submit"><?= htmlspecialchars($t->text('profile_password_update'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
          </form>

        </section>
      </div>
    </div>
  </main>
  <?php require dirname(__DIR__) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
