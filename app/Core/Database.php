<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function connection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }
        $cfg = require dirname(__DIR__, 2) . '/config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['name'],
            $cfg['charset']
        );
        try {
            self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], $cfg['options']);
        } catch (PDOException $e) {
            Logger::error('DB connect gagal', ['msg' => $e->getMessage()]);
            throw $e;
        }
        return self::$instance;
    }

    /** Koneksi ke DB testing (dipakai PHPUnit). */
    public static function testConnection(): PDO
    {
        $cfg = require dirname(__DIR__, 2) . '/config/database.php';
        $cfg['name'] = 'sipandhalu_test';
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['name'],
            $cfg['charset']
        );
        return new PDO($dsn, $cfg['user'], $cfg['pass'], $cfg['options']);
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
