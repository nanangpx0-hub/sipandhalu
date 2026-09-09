<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

final class RoleMiddleware
{
    /** @param string ...$allowed contoh: new RoleMiddleware('ADMIN') via closure di routes */
    public function __construct(private array $allowed = [])
    {
    }

    public function handle(Request $request): void
    {
        Session::start();
        $user = $_SESSION['user'] ?? null;
        if ($user === null) {
            header('Location: /login');
            exit;
        }
        if ($this->allowed !== [] && !in_array($user['role_code'] ?? '', $this->allowed, true)) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.phtml';
            exit;
        }
    }
}
