<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'app'): void
    {
        extract($data);
        $viewFile = base_path("app/Views/{$view}.php");

        if (!file_exists($viewFile)) {
            http_response_code(500);
            exit("View não encontrada: {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = base_path("app/Views/layouts/{$layout}.php");
            require $layoutFile;
            return;
        }

        echo $content;
    }

    public static function partial(string $partial, array $data = []): void
    {
        extract($data);
        require base_path("app/Views/partials/{$partial}.php");
    }
}
