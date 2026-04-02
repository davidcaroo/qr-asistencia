<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/admin'): void
    {
        $viewFile = base_path('app/Views/' . $view . '.php');

        if (!is_file($viewFile)) {
            throw new \RuntimeException('Vista no encontrada: ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = base_path('app/Views/' . $layout . '.php');

        if (!is_file($layoutFile)) {
            echo $content;
            return;
        }

        require $layoutFile;
    }
}
