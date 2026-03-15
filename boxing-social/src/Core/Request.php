<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function header(string $name, ?string $default = null): ?string
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return isset($_SERVER[$serverKey]) ? (string) $_SERVER[$serverKey] : $default;
    }

    public function expectsJson(): bool
    {
        $requestedWith = $this->header('X-Requested-With', '');
        if (strcasecmp($requestedWith, 'XMLHttpRequest') === 0) {
            return true;
        }

        $accept = $this->header('Accept', '');

        return stripos($accept, 'application/json') !== false;
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = (string) (parse_url($uri, PHP_URL_PATH) ?? '/');

        // Normalize base path when app is served from a subdirectory like /public.
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath !== '' && $basePath !== '/') {
            if ($path === $basePath) {
                $path = '/';
            } elseif (str_starts_with($path, $basePath . '/')) {
                $path = substr($path, strlen($basePath));
            }
        }

        return rtrim($path ?: '/', '/') ?: '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}
