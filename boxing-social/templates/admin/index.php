<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Administration</title>
  <link rel="stylesheet" href="/css/admin-index.css">
</head>
<body>
  <main class="page">
    <h1>Tableau de bord administration</h1>

    <p>
      <a href="/">Accueil</a> |
      <a href="/posts">Publications</a> |
      <a href="/notifications">Notifications</a>
    </p>

    <?php if (!empty($success)): ?>
      <p class="msg-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $error): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    <?php endif; ?>

    <section class="card">
      <h2>Utilisateurs</h2>
      <table>
        <thead>
          <tr><th>ID</th><th>Pseudo</th><th>Email</th><th>Role</th><th>Etat</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int) $u['id'] ?></td>
              <td><?= htmlspecialchars((string) $u['username'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) $u['role'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= ((int) $u['is_active'] === 1) ? 'actif' : 'desactive' ?></td>
              <td>
                <form method="post" action="/admin/users/toggle">
                  <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                  <input type="hidden" name="is_active" value="<?= ((int) $u['is_active'] === 1) ? '0' : '1' ?>">
                  <button type="submit"><?= ((int) $u['is_active'] === 1) ? 'Desactiver' : 'Activer' ?></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <section class="card">
      <h2>Publications</h2>
      <table>
        <thead>
          <tr><th>ID</th><th>Auteur</th><th>Titre</th><th>Visibilite</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $p): ?>
            <tr>
              <td><?= (int) $p['id'] ?></td>
              <td><?= htmlspecialchars((string) $p['username'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) ($p['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) $p['visibility'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <form method="post" action="/admin/posts/delete" onsubmit="return confirm('Supprimer ce post ?');">
                  <input type="hidden" name="post_id" value="<?= (int) $p['id'] ?>">
                  <button type="submit">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <section class="card">
      <h2>Commentaires</h2>
      <table>
        <thead>
          <tr><th>ID</th><th>Publication</th><th>Auteur</th><th>Contenu</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($comments as $c): ?>
            <tr>
              <td><?= (int) $c['id'] ?></td>
              <td>#<?= (int) $c['post_id'] ?></td>
              <td><?= htmlspecialchars((string) $c['username'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string) $c['content'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <form method="post" action="/admin/comments/delete" onsubmit="return confirm('Supprimer ce commentaire ?');">
                  <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                  <button type="submit">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <section class="card">
      <h2>Journal administration</h2>
      <?php if (empty($logs)): ?>
        <p>Aucune action admin.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>Date</th><th>Administrateur</th><th>Action</th><th>Cible</th></tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td><?= htmlspecialchars((string) $log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $log['admin_username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $log['action'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $log['target_type'], ENT_QUOTES, 'UTF-8') ?> #<?= (int) ($log['target_id'] ?? 0) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      <p class="muted">Historique des actions sensibles pour audit.</p>
    </section>
  </main>
</body>
</html>
