<?php
declare(strict_types=1);

namespace App\Core;

final class Security
{
    public const CSRF_SESSION_KEY = 'csrf_token';
    public const CSRF_FIELD = '_csrf';
    public const CSRF_HEADER = 'X-CSRF-Token';
    private static ?string $cspNonce = null;

    public static function configureErrorHandling(array $env): void
    {
        $debug = self::isTruthy($env['APP_DEBUG'] ?? '0');

        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
    }

    public static function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    public static function isHttps(string $appUrl = ''): bool
    {
        $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        $appUrlScheme = parse_url($appUrl, PHP_URL_SCHEME);

        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
            || $forwardedProto === 'https'
            || $appUrlScheme === 'https'
        );
    }

    public static function applySecurityHeaders(bool $isHttps, array $env = []): void
    {
        header_remove('X-Powered-By');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-Permitted-Cross-Domain-Policies: none');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Origin-Agent-Cluster: ?1');
        header('Content-Security-Policy: ' . self::buildCsp($isHttps, $env));

        if ($isHttps) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    public static function cspNonce(): string
    {
        if (self::$cspNonce === null) {
            self::$cspNonce = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
        }

        return self::$cspNonce;
    }

    public static function cspNonceAttr(): string
    {
        return ' nonce="' . htmlspecialchars(self::cspNonce(), ENT_QUOTES, 'UTF-8') . '"';
    }

    public static function ensureCsrfToken(): string
    {
        $token = (string) ($_SESSION[self::CSRF_SESSION_KEY] ?? '');
        if ($token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::CSRF_SESSION_KEY] = $token;

        return $token;
    }

    public static function csrfToken(): string
    {
        return (string) ($_SESSION[self::CSRF_SESSION_KEY] ?? '');
    }

    public static function requestHasValidCsrf(Request $request): bool
    {
        $sessionToken = self::csrfToken();
        if ($sessionToken === '') {
            return false;
        }

        $providedToken = (string) $request->input(self::CSRF_FIELD, '');
        if ($providedToken === '') {
            $providedToken = (string) $request->header(self::CSRF_HEADER, '');
        }

        return $providedToken !== '' && hash_equals($sessionToken, $providedToken);
    }

    public static function requestHasTrustedOrigin(Request $request, string $appUrl = ''): bool
    {
        $originHeader = trim((string) $request->header('Origin', ''));
        $refererHeader = trim((string) $request->header('Referer', ''));

        if ($originHeader === '' && $refererHeader === '') {
            return true;
        }

        $allowedOrigins = array_filter([
            self::normalizeOrigin($appUrl),
            self::normalizeOrigin(self::currentOrigin($appUrl)),
        ]);

        if ($allowedOrigins === []) {
            return true;
        }

        if ($originHeader !== '') {
            return in_array(self::normalizeOrigin($originHeader), $allowedOrigins, true);
        }

        return in_array(self::normalizeOrigin($refererHeader), $allowedOrigins, true);
    }

    public static function shouldInjectCsrfIntoResponse(string $output): bool
    {
        if ($output === '') {
            return false;
        }

        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Type:') !== 0) {
                continue;
            }

            return stripos($header, 'text/html') !== false;
        }

        $trimmed = ltrim($output);

        return str_starts_with($trimmed, '<!doctype html')
            || str_starts_with($trimmed, '<html')
            || str_starts_with($trimmed, '<');
    }

    public static function injectCsrfIntoHtml(string $html): string
    {
        $token = self::csrfToken();
        if ($token === '' || stripos($html, 'name="' . self::CSRF_FIELD . '"') !== false) {
            return $html;
        }

        $field = '<input type="hidden" name="' . self::CSRF_FIELD . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';

        return (string) preg_replace_callback(
            '~<form\b(?=[^>]*\bmethod\s*=\s*(["\'])post\1)[^>]*>~i',
            static fn(array $matches): string => $matches[0] . $field,
            $html
        );
    }

    private static function buildCsp(bool $isHttps, array $env): string
    {
        $frameSrc = ["'none'"];
        $fontSrc = ["'self'", 'data:', 'https://fonts.gstatic.com'];
        $connectSrc = ["'self'"];
        $scriptSrc = ["'self'"];
        $styleSrc = ["'self'"];
        $styleSrcElem = ["'self'", 'https://fonts.googleapis.com'];

        if (self::isTruthy(($env['RECAPTCHA_ENABLED'] ?? '1')) && self::isRecaptchaConfigured($env)) {
            $frameSrc = [
                'https://www.google.com/recaptcha/',
                'https://recaptcha.google.com/recaptcha/',
            ];
            $connectSrc[] = 'https://www.google.com/recaptcha/';
            $scriptSrc[] = 'https://www.google.com/recaptcha/';
            $scriptSrc[] = 'https://www.gstatic.com/recaptcha/';
            $styleSrc[] = "'unsafe-inline'";
        }

        if (self::isFirebaseClientConfigured($env)) {
            $connectSrc[] = 'https://firestore.googleapis.com';
            $connectSrc[] = 'https://firebaseinstallations.googleapis.com';
            $connectSrc[] = 'https://www.googleapis.com';
            $scriptSrc[] = 'https://www.gstatic.com/firebasejs/';
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            'frame-src ' . implode(' ', $frameSrc),
            "manifest-src 'self'",
            "img-src 'self' data: blob:",
            'font-src ' . implode(' ', $fontSrc),
            "media-src 'self' blob:",
            "worker-src 'self' blob:",
            'connect-src ' . implode(' ', $connectSrc),
            'script-src ' . implode(' ', $scriptSrc),
            "script-src-attr 'none'",
            'style-src ' . implode(' ', $styleSrc),
            'style-src-elem ' . implode(' ', $styleSrcElem),
            "style-src-attr 'unsafe-inline'",
        ];

        if ($isHttps && !self::isTruthy($env['APP_DEBUG'] ?? '0')) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }

    private static function isRecaptchaConfigured(array $env): bool
    {
        return trim((string) ($env['RECAPTCHA_SITE_KEY'] ?? '')) !== ''
            && trim((string) ($env['RECAPTCHA_SECRET_KEY'] ?? '')) !== '';
    }

    private static function isFirebaseClientConfigured(array $env): bool
    {
        return trim((string) ($env['FIREBASE_API_KEY'] ?? '')) !== ''
            && trim((string) ($env['FIREBASE_PROJECT_ID'] ?? '')) !== ''
            && trim((string) ($env['FIREBASE_APP_ID'] ?? '')) !== '';
    }

    private static function currentOrigin(string $appUrl = ''): string
    {
        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === '') {
            return $appUrl;
        }

        $scheme = self::isHttps($appUrl) ? 'https' : 'http';

        return $scheme . '://' . $host;
    }

    private static function normalizeOrigin(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return '';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($scheme === '' || $host === '') {
            return '';
        }

        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

        return $scheme . '://' . $host . $port;
    }
}
