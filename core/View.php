<?php

namespace Core;

class View {
    public static function render($page, $data = [], $layout = 'default') {
        extract($data);
        $pagePath = __DIR__ . "/../app/pages/{$page}.php";
        
        if (!file_exists($pagePath)) {
            http_response_code(404);
            $pagePath = __DIR__ . "/../app/pages/errors/404.php";
            
            if (!file_exists($pagePath)) {
                die("Page not found: $page");
            }
        }
        
        ob_start();
        require $pagePath;
        $content = ob_get_clean();

        if (file_exists(__DIR__ . "/../app/layouts/{$layout}.php")) {
            require __DIR__ . "/../app/layouts/{$layout}.php";
        } else {
            echo $content;
        }
    }
}