# Boxing Social - Developer README

README orienté reprise technique du projet.

Présentation du projet global. Documente le fonctionnement interne, le setup local et les conventions de développement dans `boxing-social/`.

## Stack

Backend :

- `PHP 8`
- `MySQL`
- `PDO`
- autoload `PSR-4` via Composer

Frontend :

- templates PHP rendus côté serveur
- JavaScript vanilla
- interactions AJAX via `fetch`
- sanitation HTML dynamique via `DOMPurify`

Sécurité :

- `CSRF`
- `CSP`
- validation serveur des entrées
- rate limiting
- headers HTTP de durcissement

## Arborescence utile

```text
boxing-social/
├── .env.example
├── composer.json
├── migrations/
├── public/
│   ├── css/
│   ├── img/
│   ├── js/
│   ├── uploads/
│   └── index.php
├── src/
│   ├── Controllers/
│   ├── Core/
│   ├── Database/
│   ├── Models/
│   └── Services/
├── templates/
└── var/
```

## Points d'entrée importants

- front controller : `public/index.php`
- connexion base : `src/Database/Database.php`
- sécurité HTTP / CSP / CSRF : `src/Core/Security.php`
- rate limiting : `src/Core/RateLimiter.php`
- abstraction requête HTTP : `src/Core/Request.php`
- rendu / JSON / redirections : `src/Core/Response.php`

## Architecture

Le projet repose sur un `front controller` unique :

- toute requête arrive dans `public/index.php`
- le `.env` est chargé manuellement
- la session est initialisée
- les headers de sécurité sont appliqués
- les protections `CSRF` et `Origin/Referer` sont validées
- un rate limit est appliqué sur certaines routes `POST`
- les routes sont déclarées explicitement
- les contrôleurs appellent modèles et services

L'architecture est un `MVC léger` :

- `Controllers/` : logique d'action HTTP
- `Models/` : accès aux données
- `Services/` : intégrations externes ou logique transverse
- `templates/` : rendu HTML

## Installation locale

### 1. Dépendances

Prérequis :

- `PHP 8.x`
- `MySQL`
- extension `pdo_mysql`
- `Composer`
- `Node.js` uniquement si tu veux valider la syntaxe JS avec `node --check`

### 2. Configuration

Copier `.env.example` vers `.env`.

Variables minimales :

```env
APP_ENV=dev
APP_DEBUG=1
APP_URL=http://localhost

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=boxing_social
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

TRUST_PROXY=0
RATE_LIMIT_STORAGE_DIR=

FIREBASE_DATABASE_URL=
FIREBASE_DATABASE_SECRET=

SPORTS_DATA_API_KEY=
```

### 3. Composer

```bash
composer install
```

`composer.json` est volontairement minimal. Il sert surtout à l'autoload :

```json
"autoload": {
  "psr-4": {
    "App\\\\": "src/"
  }
}
```

### 4. Base de données

Créer la base `boxing_social`, puis appliquer les scripts de `migrations/`.

Le projet utilise `PDO` avec vraies requêtes préparées :

- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
- `PDO::ATTR_EMULATE_PREPARES => false`

### 5. Web root

Le document root doit pointer vers :

```text
boxing-social/public
```

Ne jamais exposer directement la racine `boxing-social/`.

## Flux d'une requête

Flux standard :

1. requête HTTP vers `public/index.php`
2. chargement `.env`
3. sécurité session / headers
4. validation `CSRF` et origine pour les `POST`
5. rate limiting sur routes sensibles
6. dispatch route -> contrôleur
7. contrôleur -> modèle / service
8. réponse HTML ou JSON

## Conventions AJAX

Quand une interaction doit être sans rechargement :

- utiliser `fetch`
- envoyer `X-Requested-With: XMLHttpRequest`
- envoyer `X-CSRF-Token`
- rester en `credentials: 'same-origin'`
- préférer `cache: 'no-store'`

La détection AJAX côté serveur repose sur :

- `Request::expectsJson()`

Cela permet de renvoyer soit :

- une redirection / HTML
- soit un JSON exploitable côté JS

## Fichiers front importants

- `public/js/post-interactions.js`
- `public/js/social-interactions.js`
- `public/js/messages-interactions.js`
- `public/js/app-security.js`

Ces scripts gèrent :

- likes
- commentaires
- amis
- notifications
- messagerie
- aperçu popup du feed
- durcissement front basique

## Sécurité en place

### Sessions

- cookie de session `HttpOnly`
- `SameSite=Lax`
- `Secure` seulement en HTTPS
- `session.use_only_cookies=1`
- `session.use_strict_mode=1`

### CSRF

- token généré en session
- champ caché injecté automatiquement dans les formulaires `POST`
- header `X-CSRF-Token` pour les appels AJAX

### CSP

La `CSP` est envoyée côté backend par `Security::applySecurityHeaders()`.

Le projet évite désormais :

- scripts inline exécutables
- attributs `onsubmit`, `onclick`, etc.

### XSS

Protection actuelle :

- échappement HTML serveur
- réduction des injections HTML côté JS
- `DOMPurify` pour les rares fragments HTML générés dynamiquement

### Validation des entrées

Voir :

- `src/Core/InputValidator.php`

Principes :

- email normalisé et validé
- mot de passe fort
- validation serveur prioritaire
- contrôles HTML seulement en renfort

### Rate limiting

Routes protégées actuellement :

- `POST /login`
- `POST /register`
- `POST /contact`

Le stockage par défaut se fait dans :

```text
var/rate_limits
```

Important :

- ce mécanisme est fichier-local
- il fonctionne bien sur une VM ou un hébergement persistant
- il n'est pas adapté tel quel à un runtime stateless distribué

## Stockage local et implications

Le projet écrit localement dans :

- `public/uploads/avatars`
- `public/uploads/posts`
- `var/rate_limits`

Conséquences :

- la persistance disque est nécessaire
- un hébergement entièrement stateless demandera une refonte :
  - object storage pour les uploads
  - store partagé pour le rate limiting

## Intégrations externes

### Contact

Le formulaire contact passe maintenant côté serveur, puis est relayé vers Firebase Realtime Database.

Variables utiles :

- `FIREBASE_DATABASE_URL`
- `FIREBASE_DATABASE_SECRET`

Service :

- `src/Services/FirebaseContactService.php`

### MMA

Le feed peut afficher des infos MMA via une API externe.

Variable utile :

- `SPORTS_DATA_API_KEY`

Service :

- `src/Services/SportsDataService.php`

Contrôleur :

- `src/Controllers/SportsController.php`

## Vérifications rapides

### Syntaxe PHP

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

### Syntaxe JS

```bash
find public/js -name '*.js' -print0 | xargs -0 -n1 node --check
```

## Conventions de développement

- garder les contrôleurs focalisés sur HTTP + orchestration
- pousser la logique d'accès données dans `Models/`
- pousser la logique transverse ou externe dans `Services/`
- éviter les scripts inline dans les templates
- conserver les réponses AJAX compatibles fallback HTML si possible
- ne pas contourner les helpers `Security`, `Request`, `Response`

## Points d'attention

- `APP_DEBUG=0` en production
- `APP_URL` doit être exact, surtout pour HTTPS et validation d'origine
- si l'app est derrière un proxy ou load balancer, `TRUST_PROXY=1`
- les uploads doivent rester sous contrôle de taille et type MIME
- les secrets présents dans l'historique Git doivent être considérés comme compromis et régénérés avant mise en production

## Déploiement recommandé

Le projet est aujourd'hui plus cohérent sur :

- une VM PHP/MySQL avec stockage persistant

Il est moins naturel, en l'état, sur :

- un runtime totalement stateless
- un hébergement mutualisé très contraint

## Travail restant technique

À moyen terme, si tu veux faire monter le niveau :

- vraie suite de tests
- CI/CD
- stockage externe pour les uploads
- store partagé pour le rate limiting
- séparation DB / app / fichiers

