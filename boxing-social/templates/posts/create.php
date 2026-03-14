<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('posts_create_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260314b">
  <link rel="stylesheet" href="/css/posts-create.css?v=20260313m">
</head>
<body class="app-shell">
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-navbar.php'; ?>
  <main class="post-create-page app-main">
    <section class="post-create-card">
      <div class="post-create-card__head">
        <div>
          <h2><?= htmlspecialchars($t->text('posts_create_form_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
          <p><?= htmlspecialchars($t->text('posts_create_note'), ENT_QUOTES, 'UTF-8') ?></p>
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

      <form class="post-create-form" method="post" action="/posts" enctype="multipart/form-data">
        <div class="post-create-grid">
          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_type_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="post_type" required>
              <option value="publication"><?= htmlspecialchars($t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="entrainement"><?= htmlspecialchars($t->text('posts_type_training'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>

          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_visibility_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="visibility">
              <option value="public"><?= htmlspecialchars($t->text('visibility_public'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="friends"><?= htmlspecialchars($t->text('visibility_friends'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="private"><?= htmlspecialchars($t->text('visibility_private'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>
        </div>

        <label class="post-create-field">
          <span><?= htmlspecialchars($t->text('posts_title_label'), ENT_QUOTES, 'UTF-8') ?></span>
          <input type="text" name="title" placeholder="<?= htmlspecialchars($t->text('posts_title_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="post-create-field">
          <span><?= htmlspecialchars($t->text('posts_content_label'), ENT_QUOTES, 'UTF-8') ?></span>
          <textarea name="content" rows="7" placeholder="<?= htmlspecialchars($t->text('posts_content_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
        </label>

        <div class="post-create-grid">
          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_location_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="text" name="location" placeholder="<?= htmlspecialchars($t->text('posts_location_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
          </label>

          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_scheduled_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="datetime-local" name="scheduled_at">
          </label>
        </div>

        <label class="post-create-field">
          <span><?= htmlspecialchars($t->text('posts_image_label'), ENT_QUOTES, 'UTF-8') ?></span>
          <input type="file" name="post_image" accept=".jpg,.jpeg,.png,.webp">
        </label>

        <div class="post-create-actions">
          <button type="submit"><?= htmlspecialchars($t->text('posts_publish'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
      </form>
    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
