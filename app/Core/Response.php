<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function redirect(string $to): void
    {
        header('Location: ' . $to);
        exit;
    }

    public static function view(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data, EXTR_SKIP);
        $file = dirname(__DIR__) . '/Views/' . $template;
        require $file;
        exit;
    }

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
