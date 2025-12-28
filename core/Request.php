<?php

namespace Core;

class Request {
    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public function uri() {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
    
    public function get($key = null, $default = null) {
        if ($key === null) return $_GET;
        return $_GET[$key] ?? $default;
    }
    
    public function post($key = null, $default = null) {
        if ($key === null) return $_POST;
        return $_POST[$key] ?? $default;
    }
    
    public function all() {
        return array_merge($_GET, $_POST);
    }
    
    public function input($key, $default = null) {
        return $this->all()[$key] ?? $default;
    }
    
    public function json() {
        return json_decode(file_get_contents('php://input'), true);
    }
    
    public function header($key) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? null;
    }
    
    public function hasFile($key) {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }
    
    public function file($key) {
        return $_FILES[$key] ?? null;
    }
}