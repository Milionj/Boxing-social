<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function header(string $name, string $value): void
    {
        header($name . ': ' . $value);
    }

    public function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function html(string $html, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    public function redirect(string $path): void
    {
        if ($path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//')) {
            $path = '/';
        }

        header("Location: {$path}");
        exit;
    }

    public function errorPage(int $status, string $template): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');

        $file = dirname(__DIR__, 2) . '/templates/errors/' . $template . '.php';
        if (!is_file($file)) {
            echo $status . ' Error';
            return;
        }

        require $file;
    }
}
