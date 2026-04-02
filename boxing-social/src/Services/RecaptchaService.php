<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class RecaptchaService
{
    private string $siteKey;
    private string $secretKey;
    private string $verifyUrl;
    private string $expectedHost;

    public function __construct()
    {
        $this->siteKey = trim((string) ($_ENV['RECAPTCHA_SITE_KEY'] ?? ''));
        $this->secretKey = trim((string) ($_ENV['RECAPTCHA_SECRET_KEY'] ?? ''));
        $this->verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $this->expectedHost = strtolower((string) (parse_url((string) ($_ENV['APP_URL'] ?? ''), PHP_URL_HOST) ?? ''));
    }

    public function isEnabled(): bool
    {
        return $this->siteKey !== '' && $this->secretKey !== '';
    }

    public function siteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * @return array{ok:bool, reason:string}
     */
    public function verify(string $token, ?string $remoteIp = null): array
    {
        if (!$this->isEnabled()) {
            return ['ok' => true, 'reason' => 'disabled'];
        }

        $token = trim($token);
        if ($token === '') {
            return ['ok' => false, 'reason' => 'missing-token'];
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException('L’extension cURL est requise pour vérifier reCAPTCHA.');
        }

        $payload = [
            'secret' => $this->secretKey,
            'response' => $token,
        ];

        if ($remoteIp !== null && $remoteIp !== '') {
            $payload['remoteip'] = $remoteIp;
        }

        $ch = curl_init($this->verifyUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FAILONERROR => false,
        ]);

        $result = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false || $curlError !== '') {
            return ['ok' => false, 'reason' => 'verification-unavailable'];
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            return ['ok' => false, 'reason' => 'verification-unavailable'];
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded) || ($decoded['success'] ?? false) !== true) {
            return ['ok' => false, 'reason' => 'verification-failed'];
        }

        $hostname = strtolower(trim((string) ($decoded['hostname'] ?? '')));
        if ($this->expectedHost !== '' && $hostname !== '' && $hostname !== $this->expectedHost) {
            return ['ok' => false, 'reason' => 'hostname-mismatch'];
        }

        return ['ok' => true, 'reason' => 'verified'];
    }
}
