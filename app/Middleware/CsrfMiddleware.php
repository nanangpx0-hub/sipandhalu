<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;

final class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if (in_array($request->method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $sent = $request->post['_csrf'] ?? null;
            if (!Csrf::check(is_string($sent) ? $sent : null)) {
                http_response_code(419);
                require dirname(__DIR__) . '/Views/errors/419.phtml';
                exit;
            }
        }
    }
}
