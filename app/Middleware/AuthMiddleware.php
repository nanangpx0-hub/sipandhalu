<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

final class AuthMiddleware
{
    public function handle(Request $request): void
    {
        Session::start();
        if (empty($_SESSION['user'])) {
            Session::flash('error', 'Silakan login dulu.');
            header('Location: /login');
            exit;
        }
    }
}
