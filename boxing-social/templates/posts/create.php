<?php require dirname(__DIR__, 2) . '/templates/partials/app-locale.php'; ?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($t->text('posts_create_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/app-shell.css?v=20260315l">
  <link rel="stylesheet" href="/css/posts-create.css?v=20260315l">
  <script defer src="/js/post-media-preview.js?v=20260315m"></script>
  <script defer src="/js/post-form-draft.js?v=20260315m"></script>
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

      <form
        class="post-create-form"
        method="post"
        action="/posts"
        enctype="multipart/form-data"
        data-post-media-widget
        data-post-form-draft="create"
        data-restore-draft="<?= !empty($errors) ? '1' : '0' ?>"
      >
        <div class="post-create-grid">
          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_type_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="post_type" required>
              <option value="publication" <?= ($formData['post_type'] === 'publication') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('posts_type_publication'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="entrainement" <?= ($formData['post_type'] === 'entrainement') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('posts_type_training'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>

          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_visibility_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="visibility">
              <option value="public" <?= ($formData['visibility'] === 'public') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_public'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="friends" <?= ($formData['visibility'] === 'friends') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_friends'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="private" <?= ($formData['visibility'] === 'private') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('visibility_private'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>
        </div>

        <label class="post-create-field">
          <span><?= htmlspecialchars($t->text('posts_title_label'), ENT_QUOTES, 'UTF-8') ?></span>
          <input type="text" name="title" value="<?= htmlspecialchars((string) $formData['title'], ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t->text('posts_title_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="post-create-field">
          <span><?= htmlspecialchars($t->text('posts_content_label'), ENT_QUOTES, 'UTF-8') ?></span>
          <textarea name="content" rows="7" placeholder="<?= htmlspecialchars($t->text('posts_content_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required><?= htmlspecialchars((string) $formData['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <div class="post-create-grid">
          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_location_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="text" name="location" value="<?= htmlspecialchars((string) $formData['location'], ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t->text('posts_location_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
          </label>

          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_scheduled_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="datetime-local" name="scheduled_at" value="<?= htmlspecialchars((string) $formData['scheduled_at'], ENT_QUOTES, 'UTF-8') ?>">
          </label>
        </div>

        <div class="post-create-grid">
          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_media_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="file" name="post_media" accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.webm" data-post-media-input>
            <small><?= htmlspecialchars($t->text('posts_media_help'), ENT_QUOTES, 'UTF-8') ?></small>
          </label>

          <label class="post-create-field">
            <span><?= htmlspecialchars($t->text('posts_media_size_label'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="media_size">
              <option value="compact" <?= ($formData['media_size'] === 'compact') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('posts_media_size_compact'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="standard" <?= ($formData['media_size'] === 'standard') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('posts_media_size_standard'), ENT_QUOTES, 'UTF-8') ?></option>
              <option value="large" <?= ($formData['media_size'] === 'large') ? 'selected' : '' ?>><?= htmlspecialchars($t->text('posts_media_size_large'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
          </label>
        </div>

        <section class="post-media-preview is-hidden" data-post-media-preview>
          <span class="post-media-preview__label"><?= htmlspecialchars($t->text('posts_media_preview'), ENT_QUOTES, 'UTF-8') ?></span>
          <div class="post-media-preview__frame" data-post-media-preview-frame></div>
          <button type="button" class="post-media-preview__clear" data-post-media-clear>
            <?= htmlspecialchars($t->text('posts_remove_selected_media'), ENT_QUOTES, 'UTF-8') ?>
          </button>
        </section>

        <div class="post-create-actions">
          <button type="submit"><?= htmlspecialchars($t->text('posts_publish'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
      </form>

    </section>
  </main>
  <?php require dirname(__DIR__, 2) . '/templates/partials/app-footer.php'; ?>
</body>
</html>
