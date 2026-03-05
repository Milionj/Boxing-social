<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Messagerie</title>

  <style>
    /* Mise en page simple en 2 colonnes :
       - gauche : liste des conversations
       - droite : fil de discussion */
    .wrap { display: flex; gap: 24px; align-items: flex-start; }

    /* Style commun des colonnes */
    .col { border: 1px solid #ddd; padding: 12px; min-height: 300px; }

    /* Colonne gauche (liste conversations) */
    .left { width: 280px; }

    /* Colonne droite (discussion active) */
    .right { flex: 1; }

    /* Style d'un message dans le fil */
    .msg { border-bottom: 1px solid #eee; padding: 8px 0; }
  </style>
</head>
<body>
  <h1>Messagerie</h1>

  <!-- Navigation rapide -->
  <p>
    <a href="/">Accueil</a> |
    <a href="/notifications">Notifications</a> |
    <a href="/friends">Amis</a>
  </p>

  <!-- Message flash de succès (affiché une seule fois via session) -->
  <?php if (!empty($success)): ?>
    <p style="color:#067647;">
      <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </p>
  <?php endif; ?>

  <!-- Liste des erreurs flash -->
  <?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
      <p style="color:#b42318;">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </p>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="wrap">
    <!-- =========================
         COLONNE GAUCHE : Conversations
         ========================= -->
    <section class="col left">
      <h2>Conversations</h2>

      <!-- Démarrer/Ouvrir rapidement une discussion via l'ID utilisateur -->
      <form method="get" action="/messages" style="margin-bottom:12px;">
        <input type="number" name="user_id" min="1" placeholder="ID utilisateur" required>
        <button type="submit">Ouvrir</button>
      </form>

      <?php if (empty($conversations)): ?>
        <p>Aucune conversation.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($conversations as $conv): ?>
            <li>
              <!--
                On ouvre une conversation en passant user_id dans l'URL :
                /messages?user_id=ID_DE_L_AUTRE_UTILISATEUR
              -->
              <a href="/messages?user_id=<?= (int) $conv['other_user_id'] ?>">
                <?= htmlspecialchars((string) $conv['username'], ENT_QUOTES, 'UTF-8') ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <!-- =========================
         COLONNE DROITE : Discussion active
         ========================= -->
    <section class="col right">
      <h2>Discussion</h2>

      <!-- Si aucune conversation n'est sélectionnée -->
      <?php if ($selectedUserId <= 0): ?>
        <p>Choisis une conversation.</p>

        <!-- Premier envoi direct sans conversation existante -->
        <hr>
        <h3>Nouveau message</h3>
        <form method="post" action="/messages/send">
          <input type="number" name="receiver_id" min="1" placeholder="ID destinataire" required>
          <br><br>
          <textarea
            name="content"
            rows="3"
            cols="60"
            placeholder="Ecrire un premier message..."
            required
          ></textarea>
          <br>
          <button type="submit">Envoyer</button>
        </form>
      <?php else: ?>

        <!-- Si une conversation est sélectionnée mais qu'il n'y a encore aucun message -->
        <?php if (empty($thread)): ?>
          <p>Aucun message.</p>
        <?php else: ?>

          <!-- Affichage du fil de discussion -->
          <?php foreach ($thread as $m): ?>
            <div class="msg">
              <!--
                On affiche "Moi" si sender_id = id du user connecté,
                sinon "Lui/Elle"
              -->
              <strong>
                <?= ((int) $m['sender_id'] === (int) ($_SESSION['user']['id'] ?? 0)) ? 'Moi' : 'Lui/Elle' ?>:
              </strong>

              <!--
                htmlspecialchars = protection XSS
                nl2br = conserve les retours à la ligne dans l'affichage
              -->
              <?= nl2br(htmlspecialchars((string) $m['content'], ENT_QUOTES, 'UTF-8')) ?>

              <br>

              <!-- Date/heure brute (tu pourras la formater plus tard) -->
              <small><?= htmlspecialchars((string) $m['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <hr>

        <!--
          Formulaire d'envoi de message :
          - POST /messages/send
          - receiver_id = interlocuteur sélectionné
          - content = texte du message
        -->
        <form method="post" action="/messages/send">
          <input type="hidden" name="receiver_id" value="<?= (int) $selectedUserId ?>">

          <textarea
            name="content"
            rows="3"
            cols="60"
            placeholder="Ecrire un message..."
            required
          ></textarea>

          <br>
          <button type="submit">Envoyer</button>
        </form>
      <?php endif; ?>
    </section>
  </div>
</body>
</html>
