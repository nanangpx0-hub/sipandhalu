<?php

declare(strict_types=1);

namespace App\Core;

/** Router sederhana: daftarkan rute di config/routes.php */
final class Router
{
    /** @var array<int, array{method:string,path:string,handler:array{0:string,1:string},middleware:string[]}> */
    private array $routes = [];

    public function get(string $path, string $controller, string $method, array $middleware = []): void
    {
        $this->add('GET', $path, $controller, $method, $middleware);
    }

    public function post(string $path, string $controller, string $method, array $middleware = []): void
    {
        $this->add('POST', $path, $controller, $method, $middleware);
    }

    private function add(string $http, string $path, string $controller, string $method, array $middleware): void
    {
        $this->routes[] = ['method' => $http, 'path' => $path, 'handler' => [$controller, $method], 'middleware' => $middleware];
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $r) {
            $params = [];
            if ($r['method'] === $request->method && $this->match($r['path'], $request->path, $params)) {
                foreach ($r['middleware'] as $mw) {
                    /** @var object $m */
                    $m = new $mw();
                    $m->handle($request);
                }
                [$class, $method] = $r['handler'];
                $c = new $class();
                $c->$method($request, $params);
                return;
            }
        }
        http_response_code(404);
        require dirname(__DIR__) . '/Views/errors/404.phtml';
    }

    /** Dukung pola /petugas/{id} */
    private function match(string $pattern, string $path, array &$params): bool
    {
        $regex = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (preg_match($regex, $path, $m)) {
            foreach ($m as $k => $v) {
                if (is_string($k)) {
                    $params[$k] = $v;
                }
            }
            return true;
        }
        return false;
    }
}
