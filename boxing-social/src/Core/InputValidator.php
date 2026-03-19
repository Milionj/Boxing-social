<?php
declare(strict_types=1);

namespace App\Core;

final class InputValidator
{
    public const EMAIL_HTML_PATTERN = "(?=.{6,254}$)(?=.{1,64}@)[A-Za-z0-9.!#$%&'*+/=?^_`{|}~-]+@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\\.[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)+";
    public const PASSWORD_HTML_PATTERN = '(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).{12,4096}';

    private const EMAIL_REGEX = '/^(?=.{6,254}$)(?=.{1,64}@)[A-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[A-Z0-9](?:[A-Z0-9-]{0,61}[A-Z0-9])?(?:\.[A-Z0-9](?:[A-Z0-9-]{0,61}[A-Z0-9])?)+$/i';

    private function __construct()
    {
    }

    public static function normalizeEmail(string $email): string
    {
        $normalized = strtolower(trim($email));

        if (!str_contains($normalized, '@')) {
            return $normalized;
        }

        [$localPart, $domain] = explode('@', $normalized, 2);
        $domain = trim($domain);

        if ($domain !== '' && function_exists('idn_to_ascii')) {
            $asciiDomain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if (is_string($asciiDomain) && $asciiDomain !== '') {
                $domain = strtolower($asciiDomain);
            }
        }

        return trim($localPart) . '@' . $domain;
    }

    public static function isValidEmail(string $email): bool
    {
        $normalized = self::normalizeEmail($email);

        return $normalized !== ''
            && preg_match(self::EMAIL_REGEX, $normalized) === 1
            && filter_var($normalized, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return string[]
     */
    public static function passwordErrors(string $password, string $label = 'Le mot de passe'): array
    {
        $errors = [];

        if (mb_strlen($password, 'UTF-8') < 12) {
            $errors[] = $label . ' doit contenir au moins 12 caractères.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = $label . ' doit contenir au moins une majuscule.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = $label . ' doit contenir au moins une minuscule.';
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = $label . ' doit contenir au moins un chiffre.';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = $label . ' doit contenir au moins un caractère spécial.';
        }

        return $errors;
    }
}
