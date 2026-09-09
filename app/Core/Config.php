<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static array $cache = [];

    public static function loadEnv(string $basePath): void
    {
        $envFile = $basePath . '/.env';
        if (!is_file($envFile)) {
            return;
        }
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));
            if (strlen($val) >= 2 && $val[0] === '"' && $val[-1] === '"') {
                $val = substr($val, 1, -1);
            }
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
            putenv($key . '=' . $val);
        }
    }

    /** @param mixed $default */
    public static function get(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}
