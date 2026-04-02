<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\InputValidator;
use App\Core\Request;
use App\Core\Response;
use App\Services\FirebaseContactService;
use App\Services\RecaptchaService;
use App\Services\Translator;
use App\Models\UserSettings;
use RuntimeException;

final class ContactController
{
    private FirebaseContactService $contactService;
    private RecaptchaService $recaptcha;

    public function __construct()
    {
        $this->contactService = new FirebaseContactService();
        $this->recaptcha = new RecaptchaService();
    }

    public function show(Response $response): void
    {
        $success = $_SESSION['success_contact'] ?? '';
        $errors = $_SESSION['errors_contact'] ?? [];
        $old = $_SESSION['old_contact'] ?? [];
        $recaptchaSiteKey = $this->recaptcha->siteKey();
        $useClientFirebaseFallback = $this->canUseClientFirebaseFallback();
        $firebaseClientConfig = [
            'apiKey' => (string) ($_ENV['FIREBASE_API_KEY'] ?? ''),
            'authDomain' => (string) ($_ENV['FIREBASE_AUTH_DOMAIN'] ?? ''),
            'projectId' => (string) ($_ENV['FIREBASE_PROJECT_ID'] ?? ''),
            'storageBucket' => (string) ($_ENV['FIREBASE_STORAGE_BUCKET'] ?? ''),
            'messagingSenderId' => (string) ($_ENV['FIREBASE_MESSAGING_SENDER_ID'] ?? ''),
            'appId' => (string) ($_ENV['FIREBASE_APP_ID'] ?? ''),
            'measurementId' => (string) ($_ENV['FIREBASE_MEASUREMENT_ID'] ?? ''),
        ];

        unset($_SESSION['success_contact'], $_SESSION['errors_contact'], $_SESSION['old_contact']);

        require dirname(__DIR__, 2) . '/templates/contact.php';
    }

    public function submit(Request $request, Response $response): void
    {
        $email = InputValidator::normalizeEmail((string) $request->input('email', ''));
        $subject = trim((string) $request->input('subject', ''));
        $message = trim((string) $request->input('message', ''));
        $honeypot = trim((string) $request->input('website', ''));
        $recaptchaToken = (string) $request->input('g-recaptcha-response', '');

        $allowedSubjects = [
            'question_generale',
            'support_technique',
            'signalement',
        ];

        // On renvoie toujours l'état du formulaire au serveur :
        // cela permet au rate limit, à la validation et aux logs
        // de s'appliquer même sans JavaScript.
        $_SESSION['old_contact'] = [
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
        ];

        // Honeypot minimal : si rempli, on ne crée rien, on répond
        // comme si tout s'était bien passé pour ne pas entraîner le bot.
        if ($honeypot !== '') {
            unset($_SESSION['old_contact']);
            $_SESSION['success_contact'] = $this->translator()->text('contact_success_sent');
            $response->redirect('/contact');
            return;
        }

        $errors = [];

        if (!InputValidator::isValidEmail($email)) {
            $errors[] = $this->translator()->text('contact_error_invalid_email');
        }

        if (!in_array($subject, $allowedSubjects, true)) {
            $errors[] = $this->translator()->text('contact_error_invalid_subject');
        }

        if (mb_strlen($message, 'UTF-8') < 20) {
            $errors[] = $this->translator()->text('contact_error_message_short');
        }

        if (mb_strlen($message, 'UTF-8') > 4000) {
            $errors[] = $this->translator()->text('contact_error_message_long');
        }

        if ($errors === []) {
            try {
                $recaptchaResult = $this->recaptcha->verify($recaptchaToken, (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
            } catch (RuntimeException) {
                $recaptchaResult = ['ok' => false, 'reason' => 'verification-unavailable'];
            }

            if (!$recaptchaResult['ok']) {
                $errors[] = $this->recaptchaErrorMessage($recaptchaResult['reason']);
            }
        }

        if ($errors !== []) {
            $_SESSION['errors_contact'] = $errors;
            $response->redirect('/contact');
            return;
        }

        try {
            // Le message part côté serveur pour que le rate limiting
            // et les validations s'appliquent réellement.
            $this->contactService->send([
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'createdAt' => gmdate(DATE_ATOM),
                'source' => 'boxing-social-server-contact-form',
                'userId' => is_int($_SESSION['user']['id'] ?? null) ? (int) $_SESSION['user']['id'] : null,
                'username' => isset($_SESSION['user']['username']) ? (string) $_SESSION['user']['username'] : null,
                'ip' => $this->maskedIp(),
                'userAgent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]);
        } catch (RuntimeException $exception) {
            $message = $this->translator()->text('contact_error_delivery_failed');
            if (\App\Core\Security::isTruthy($_ENV['APP_DEBUG'] ?? '0')) {
                $message .= ' [' . $exception->getMessage() . ']';
            }

            $_SESSION['errors_contact'] = [$message];
            $response->redirect('/contact');
            return;
        }

        unset($_SESSION['old_contact']);
        $_SESSION['success_contact'] = $this->translator()->text('contact_success_sent');
        $response->redirect('/contact');
    }

    private function translator(): Translator
    {
        $language = 'francais';

        if (is_int($_SESSION['user']['id'] ?? null)) {
            $settings = new UserSettings();
            $language = $settings->languageForUser((int) $_SESSION['user']['id']);
        }

        return new Translator($language);
    }

    private function maskedIp(): string
    {
        $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.x.x';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 4)) . ':x:x:x:x';
        }

        return 'unknown';
    }

    private function recaptchaErrorMessage(string $reason): string
    {
        return match ($reason) {
            'missing-token' => $this->translator()->text('contact_error_recaptcha_required'),
            'verification-unavailable' => $this->translator()->text('contact_error_recaptcha_unavailable'),
            'hostname-mismatch' => $this->translator()->text('contact_error_recaptcha_failed'),
            default => $this->translator()->text('contact_error_recaptcha_failed'),
        };
    }

    private function canUseClientFirebaseFallback(): bool
    {
        $hasBackendConfig = trim((string) ($_ENV['FIREBASE_DATABASE_URL'] ?? '')) !== '';
        if ($hasBackendConfig) {
            return false;
        }

        return trim((string) ($_ENV['FIREBASE_API_KEY'] ?? '')) !== ''
            && trim((string) ($_ENV['FIREBASE_PROJECT_ID'] ?? '')) !== ''
            && trim((string) ($_ENV['FIREBASE_APP_ID'] ?? '')) !== '';
    }
}
