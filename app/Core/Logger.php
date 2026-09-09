<?php

declare(strict_types=1);

namespace App\Core;

final class Logger
{
    public static function info(string $msg, array $ctx = []): void
    {
        self::write('INFO', $msg, $ctx);
    }

    public static function warning(string $msg, array $ctx = []): void
    {
        self::write('WARNING', $msg, $ctx);
    }

    public static function error(string $msg, array $ctx = []): void
    {
        self::write('ERROR', $msg, $ctx);
    }

    private static function write(string $level, string $msg, array $ctx): void
    {
        $path = $_ENV['LOG_PATH'] ?? dirname(__DIR__, 2) . '/storage/logs/app.log';
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $line = sprintf(
            "[%s] %s %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $msg,
            $ctx === [] ? '' : json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }
}
