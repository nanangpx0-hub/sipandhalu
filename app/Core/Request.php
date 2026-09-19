<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $get,
        public readonly array $post,
        public readonly array $server,
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // dukung method spoofing _method=PUT/DELETE dari form POST
        if ($method === 'POST' && isset($_POST['_method'])) {
            $m = strtoupper((string) $_POST['_method']);
            if (in_array($m, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $m;
            }
        }
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        // jika dijalankan dari subfolder, potong base path public
        return new self($method, $path, $_GET, $_POST, $_SERVER);
    }

    public function input(string $key, ?string $default = null): ?string
    {
        $v = $this->post[$key] ?? $this->get[$key] ?? $default;
        if ($v === null) {
            return null;
        }
        return trim((string) $v);
    }

    public function query(string $key, ?string $default = null): ?string
    {
        $v = $this->get[$key] ?? $default;
        if ($v === null) {
            return null;
        }
        return trim((string) $v);
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return substr($this->server['HTTP_USER_AGENT'] ?? '', 0, 255);
    }
}
