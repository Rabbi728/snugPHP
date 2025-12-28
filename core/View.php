<?php

namespace Core;

class View {
    public static function render($page, $data = []) {
        extract($data);
        
        $pagePath = __DIR__ . "/../app/pages/{$page}.php";
        
        if (!file_exists($pagePath)) {
            http_response_code(404);
            $pagePath = __DIR__ . "/../app/pages/errors/404.php";
            
            if (!file_exists($pagePath)) {
                die("Page not found: $page");
            }
        }
        
        require $pagePath;
    }
}