<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $handler): self
    {
        $this->routes['GET'][$uri] = $handler;

        return $this;
    }

    public function post(string $uri, callable|array $handler): self
    {
        $this->routes['POST'][$uri] = $handler;

        return $this;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();
        $handler = $this->routes[$method][$uri] ?? null;

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
}
