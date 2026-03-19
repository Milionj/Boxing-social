<?php
declare(strict_types=1);

namespace App\Core;

final class RateLimiter
{
    private string $storageDir;
    private bool $trustProxy;

    public function __construct(array $env = [])
    {
        $defaultStorageDir = dirname(__DIR__, 2) . '/var/rate_limits';
        $storageDir = trim((string) ($env['RATE_LIMIT_STORAGE_DIR'] ?? $defaultStorageDir));

        $this->storageDir = $storageDir !== '' ? $storageDir : $defaultStorageDir;
        $this->trustProxy = Security::isTruthy($env['TRUST_PROXY'] ?? '0');
    }

    /**
     * @return array{allowed:bool, limit:int, remaining:int, retry_after:int, reset_at:int}
     */
    public function consume(string $bucket, int $limit, int $windowSeconds): array
    {
        $limit = max(1, $limit);
        $windowSeconds = max(1, $windowSeconds);
        $now = time();
        $storageFile = $this->storageFile($bucket);

        $this->ensureStorageDir();

        $handle = fopen($storageFile, 'c+');
        if ($handle === false) {
            return [
                'allowed' => true,
                'limit' => $limit,
                'remaining' => $limit - 1,
                'retry_after' => 0,
                'reset_at' => $now + $windowSeconds,
            ];
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                fclose($handle);
                return [
                    'allowed' => true,
                    'limit' => $limit,
                    'remaining' => $limit - 1,
                    'retry_after' => 0,
                    'reset_at' => $now + $windowSeconds,
                ];
            }

            rewind($handle);
            $raw = stream_get_contents($handle);
            $payload = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
            $attempts = [];

            if (is_array($payload['attempts'] ?? null)) {
                foreach ($payload['attempts'] as $timestamp) {
                    if (is_int($timestamp) && $timestamp > ($now - $windowSeconds)) {
                        $attempts[] = $timestamp;
                    }
                }
            }

            if (count($attempts) >= $limit) {
                $oldestAttempt = min($attempts);
                $retryAfter = max(1, ($oldestAttempt + $windowSeconds) - $now);
                $this->persist($handle, $attempts);

                return [
                    'allowed' => false,
                    'limit' => $limit,
                    'remaining' => 0,
                    'retry_after' => $retryAfter,
                    'reset_at' => $oldestAttempt + $windowSeconds,
                ];
            }

            $attempts[] = $now;
            $this->persist($handle, $attempts);

            $remaining = max(0, $limit - count($attempts));
            $resetAt = ($attempts !== []) ? min($attempts) + $windowSeconds : ($now + $windowSeconds);

            return [
                'allowed' => true,
                'limit' => $limit,
                'remaining' => $remaining,
                'retry_after' => 0,
                'reset_at' => $resetAt,
            ];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function clientFingerprint(Request $request, string $routeName): string
    {
        return strtolower($routeName) . '|' . $this->clientIp($request);
    }

    private function clientIp(Request $request): string
    {
        if ($this->trustProxy) {
            $forwardedFor = trim((string) $request->header('X-Forwarded-For', ''));
            if ($forwardedFor !== '') {
                $parts = array_map('trim', explode(',', $forwardedFor));
                foreach ($parts as $candidate) {
                    if (filter_var($candidate, FILTER_VALIDATE_IP) !== false) {
                        return $candidate;
                    }
                }
            }
        }

        $remoteAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        return filter_var($remoteAddr, FILTER_VALIDATE_IP) !== false ? $remoteAddr : 'unknown';
    }

    private function storageFile(string $bucket): string
    {
        return rtrim($this->storageDir, '/\\') . '/' . hash('sha256', $bucket) . '.json';
    }

    private function ensureStorageDir(): void
    {
        if (is_dir($this->storageDir)) {
            return;
        }

        @mkdir($this->storageDir, 0770, true);
    }

    /**
     * @param int[] $attempts
     */
    private function persist($handle, array $attempts): void
    {
        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode(['attempts' => array_values($attempts)], JSON_THROW_ON_ERROR));
        fflush($handle);
    }
}
