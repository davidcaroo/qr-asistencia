# QR Asistencia

Sistema de asistencia por QR con PHP, MySQL, Bootstrap y una base compatible con SB Admin 2.

## Alcance inicial

- QR global rotativo para abrir el flujo de marcado.
- Identificación del empleado por cédula solamente.
- Alternancia automática entrada/salida.
- Validación anti-reuso por ventana corta de tiempo.
- Horarios aplicables por grupo de empleados.
- Panel administrativo base para empleados, horarios y QR en vivo.

## Estructura

- `public/` contiene el front controller y los assets públicos.
- `app/Core/` tiene router, request, response, sesión, CSRF y base de vista.
- `app/Controllers/` maneja el flujo web.
- `app/Infrastructure/Repositories/` encapsula consultas SQL.
- `app/Services/` concentra la lógica de negocio.
- `app/Views/` contiene las pantallas del panel y del flujo de asistencia.
- `schema.sql` define la base de datos completa.

## Arranque

1. Copia `.env.example` a `.env` y ajusta credenciales, URL y secreto.
2. Importa `schema.sql` en MySQL.
3. Ejecuta `composer install`.
4. Levanta el servidor con `composer serve` o configura Apache para apuntar a `public/`.

## Nota sobre SB Admin 2

La UI ya está maquetada con un estilo compatible con SB Admin 2 y Bootstrap 4. Si quieres usar el kit visual completo, puedes montar sus assets en `public/vendor/` sin cambiar la lógica de negocio.
