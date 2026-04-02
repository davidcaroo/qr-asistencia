<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $handler): self
    {
        $this->routes['GET'][] = ['uri' => $uri, 'handler' => $handler, 'regex' => $this->compileRegex($uri)];

        return $this;
    }

    public function post(string $uri, callable|array $handler): self
    {
        $this->routes['POST'][] = ['uri' => $uri, 'handler' => $handler, 'regex' => $this->compileRegex($uri)];

        return $this;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();
        $routes = $this->routes[$method] ?? [];
        $handler = null;

        foreach ($routes as $route) {
            if ($route['uri'] === $uri) {
                $handler = $route['handler'];
                $request->setRouteParams([]);
                break;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                $handler = $route['handler'];
                $params = array_filter(
                    $matches,
                    static fn($key): bool => is_string($key),
                    ARRAY_FILTER_USE_KEY
                );
                $request->setRouteParams($params);
                break;
            }
        }

        if ($handler === null) {
            Response::error(404, [
                'details' => 'No existe una ruta configurada para ' . $method . ' ' . $uri . '.',
                'actionLabel' => 'Volver al marcador',
                'actionUrl' => site_url('marcar'),
                'helpText' => 'Verifica la dirección o vuelve a la pantalla principal de asistencia.',
            ]);
        }

        if (is_array($handler) && is_string($handler[0])) {
            $controller = new $handler[0]();
            $methodName = $handler[1];
            $controller->{$methodName}($request);

            return;
        }

        $handler($request);
    }

    private function compileRegex(string $uri): string
    {
        $escaped = preg_quote($uri, '#');
        $escaped = preg_replace('#\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}#', '(?P<$1>[^/]+)', $escaped) ?? $escaped;

        return '#^' . $escaped . '$#';
    }
}
