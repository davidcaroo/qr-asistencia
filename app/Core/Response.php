<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function redirect(string $path): never
    {
        header('Location: ' . site_url($path));
        exit;
    }

    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error(int $status, array $data = []): never
    {
        http_response_code($status);

        $defaults = [
            400 => ['title' => 'Solicitud incorrecta', 'message' => 'La petición no se pudo procesar.'],
            401 => ['title' => 'Acceso no autorizado', 'message' => 'Debes iniciar sesión para continuar.'],
            403 => ['title' => 'Acceso denegado', 'message' => 'No tienes permisos para ver este recurso.'],
            404 => ['title' => 'Página no encontrada', 'message' => 'La ruta solicitada no existe en este sistema.'],
            405 => ['title' => 'Método no permitido', 'message' => 'La acción solicitada no está disponible para esta ruta.'],
            408 => ['title' => 'Tiempo de espera agotado', 'message' => 'La solicitud tardó demasiado en completarse.'],
            429 => ['title' => 'Demasiadas solicitudes', 'message' => 'Se hicieron demasiadas peticiones en poco tiempo.'],
            500 => ['title' => 'Error interno del servidor', 'message' => 'Ocurrió un problema inesperado al procesar tu solicitud.'],
            502 => ['title' => 'Puerta de enlace incorrecta', 'message' => 'El servidor recibió una respuesta inválida.'],
            503 => ['title' => 'Servicio no disponible', 'message' => 'El sistema está temporalmente fuera de servicio.'],
            504 => ['title' => 'Tiempo de espera agotado', 'message' => 'El servidor no respondió a tiempo.'],
        ];

        $copy = $defaults[$status] ?? [
            'title' => 'Error',
            'message' => 'Se produjo un error inesperado.',
        ];

        View::render('errors/show', array_merge([
            'statusCode' => $status,
            'pageTitle' => $copy['title'],
            'title' => $copy['title'],
            'message' => $copy['message'],
            'details' => $data['details'] ?? null,
            'actionLabel' => $data['actionLabel'] ?? 'Ir al inicio',
            'actionUrl' => $data['actionUrl'] ?? site_url('marcar'),
            'helpText' => $data['helpText'] ?? 'Si el problema persiste, revisa la URL o vuelve a intentar.',
        ], $data), 'layouts/error');

        exit;
    }
}
