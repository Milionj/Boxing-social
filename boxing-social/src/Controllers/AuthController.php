<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\InputValidator;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

/**
 * Principe important : POST -> redirect -> GET (pattern PRG)
 */
final class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        // Service d'auth (DB + password_hash/verify)
        $this->auth = new AuthService();
    }

    /**
     * GET /register
     * - Récupère les erreurs et anciennes valeurs depuis la session
     * - Puis les supprime (flash message : affiché une seule fois)
     * - Charge le template
     */
    public function showRegister(Response $response): void
    {
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];

        // Important : on vide après lecture pour éviter d'afficher les mêmes erreurs au refresh
        unset($_SESSION['errors'], $_SESSION['old']);

        require dirname(__DIR__, 2) . '/templates/register.php';
    }

    /**
     * POST /register
     * - Validation des champs
     * - Vérifie unicité username/email
     * - Si erreurs : stocke en session + redirect /register
     * - Si OK : crée l'utilisateur + message success + redirect /login
     */
    public function register(Request $request, Response $response): void
    {
        // Nettoyage (trim) + normalisation email en lowercase
        $username = trim((string) $request->input('username', ''));
        $email = InputValidator::normalizeEmail((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $passwordConfirm = (string) $request->input('password_confirm', '');

        $errors = [];

        // Règles pseudo : longueur + caractères autorisés
        if ($username === '' || strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = 'Le pseudo doit contenir entre 3 et 30 caractères.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Le pseudo ne doit contenir que lettres, chiffres et underscore.';
        }

        // Validation email
        if (!InputValidator::isValidEmail($email)) {
            $errors[] = 'Email invalide.';
        }

        $errors = [...$errors, ...InputValidator::passwordErrors($password, 'Le mot de passe')];

        // Confirmation mot de passe
        if ($password !== $passwordConfirm) {
            $errors[] = 'La confirmation du mot de passe ne correspond pas.';
        }

        // Vérifications DB : unicité username/email
        // (On le fait après les validations basiques pour éviter des requêtes inutiles)
        if ($this->auth->usernameExists($username)) {
            $errors[] = 'Ce pseudo est déjà utilisé.';
        }
        if ($this->auth->emailExists($email)) {
            $errors[] = 'Cet email est déjà utilisé.';
        }

        // Si erreurs : on stocke et on redirige vers le formulaire
        if ($errors !== []) {
            $_SESSION['errors'] = $errors;

            // old sert à re-remplir le formulaire (sauf le password)
            $_SESSION['old'] = ['username' => $username, 'email' => $email];

            $response->redirect('/register');
            return;
        }

        // Si tout est OK, on crée le compte
        $this->auth->register($username, $email, $password);

        // Message flash pour la page login
        $_SESSION['success'] = 'Compte créé. Vous pouvez vous connecter.';

        $response->redirect('/login');
    }

    /**
     * GET /login
     * - Récupère errors/success/old (flash)
     * - Les supprime ensuite
     * - Affiche la page login
     */
    public function showLogin(Response $response): void
    {
        $errors = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? '';
        $old = $_SESSION['old'] ?? [];

        unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old']);

        require dirname(__DIR__, 2) . '/templates/login.php';
    }

    /**
     * POST /login
     * - Validation minimale
     * - Appel AuthService::attemptLogin()
     * - Si échec : errors + old + redirect /login
     * - Si OK : redirect /
     */
    public function login(Request $request, Response $response): void
    {
        $email = InputValidator::normalizeEmail((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $errors = [];

        if (!InputValidator::isValidEmail($email)) {
            $errors[] = 'Email invalide.';
        }
        if ($password === '') {
            $errors[] = 'Mot de passe obligatoire.';
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['email' => $email];
            $response->redirect('/login');
            return;
        }

        // Vérifie email + mot de passe en base
        if (!$this->auth->attemptLogin($email, $password)) {
            $_SESSION['errors'] = ['Identifiants invalides.'];
            $_SESSION['old'] = ['email' => $email];
            $response->redirect('/login');
            return;
        }

        $response->redirect('/');
    }

    /**
     * Déconnexion
     * Recommandation : POST /logout (plutôt que GET) + CSRF
     */
    public function logout(Response $response): void
    {
        $this->auth->logout();
        $response->redirect('/login?logged_out=1');
    }
}
