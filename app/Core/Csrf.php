<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        Session::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        $t = self::token();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function check(?string $sent): bool
    {
        Session::start();
        $saved = $_SESSION['csrf_token'] ?? '';
        if ($saved === '' || $sent === null || $sent === '') {
            return false;
        }
        return hash_equals($saved, $sent);
    }
}
