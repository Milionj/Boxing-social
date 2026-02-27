<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Inscription</title></head>
<body>
  <h1>Inscription</h1>
  <form method="post" action="/register">
    <input name="username" placeholder="Pseudo" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Mot de passe" required>
    <button type="submit">Créer mon compte</button>
  </form>
  <a href="/login">Déjà un compte ? Connexion</a>
</body>
</html>
