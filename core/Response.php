<?php

namespace Core;

class Response {
    public function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    public function redirect($path) {
        header("Location: $path");
        exit;
    }
    
    public function status($code) {
        http_response_code($code);
        return $this;
    }
    
    public function send($content, $status = 200) {
        http_response_code($status);
        echo $content;
    }
}