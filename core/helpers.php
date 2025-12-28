<?php

use Core\View;
use Core\Response;
use Core\Request;
use Core\Session;
use Core\Database;

/**
 * Dump and Die - Output variable and stop execution
 */
if (!function_exists('dd')) {
    function dd(...$vars) {
        echo '<style>
            .dd-container { 
                background: #1e1e1e; 
                color: #d4d4d4; 
                padding: 20px; 
                margin: 10px; 
                border-radius: 5px;
                font-family: "Consolas", "Monaco", monospace;
                font-size: 14px;
                line-height: 1.5;
            }
            .dd-title { 
                color: #4ec9b0; 
                font-weight: bold; 
                margin-bottom: 10px;
                border-bottom: 2px solid #4ec9b0;
                padding-bottom: 5px;
            }
            .dd-trace {
                color: #808080;
                font-size: 12px;
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #333;
            }
        </style>';
        
        foreach ($vars as $var) {
            echo '<div class="dd-container">';
            echo '<div class="dd-title">Dump and Die</div>';
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
            
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
            echo '<div class="dd-trace">';
            echo 'File: ' . ($trace['file'] ?? 'unknown') . '<br>';
            echo 'Line: ' . ($trace['line'] ?? 'unknown');
            echo '</div>';
            echo '</div>';
        }
        
        die();
    }
}

/**
 * Dump - Output variable without stopping execution
 */
if (!function_exists('dump')) {
    function dump(...$vars) {
        echo '<style>
            .dump-container { 
                background: #f8f8f8; 
                color: #333; 
                padding: 15px; 
                margin: 10px; 
                border: 2px solid #ddd;
                border-radius: 5px;
                font-family: "Consolas", "Monaco", monospace;
                font-size: 13px;
            }
            .dump-title { 
                color: #0066cc; 
                font-weight: bold; 
                margin-bottom: 8px;
            }
        </style>';
        
        foreach ($vars as $var) {
            echo '<div class="dump-container">';
            echo '<div class="dump-title">Dump</div>';
            echo '<pre>';
            print_r($var);
            echo '</pre>';
            echo '</div>';
        }
    }
}

/**
 * View helper
 */
if (!function_exists('view')) {
    function view($page, $data = []) {
        return View::render($page, $data);
    }
}

/**
 * Redirect helper
 */
if (!function_exists('redirect')) {
    function redirect($path) {
        $response = new Response();
        return $response->redirect($path);
    }
}

/**
 * JSON response helper
 */
if (!function_exists('json')) {
    function json($data, $status = 200) {
        $response = new Response();
        return $response->json($data, $status);
    }
}

/**
 * Request helper
 */
if (!function_exists('request')) {
    function request($key = null, $default = null) {
        $req = new Request();
        if ($key === null) {
            return $req;
        }
        return $req->input($key, $default);
    }
}

/**
 * Session helpers
 */
if (!function_exists('session')) {
    function session($key = null, $default = null) {
        if ($key === null) {
            return new Session();
        }
        return Session::get($key, $default);
    }
}

if (!function_exists('session_set')) {
    function session_set($key, $value) {
        return Session::set($key, $value);
    }
}

if (!function_exists('session_has')) {
    function session_has($key) {
        return Session::has($key);
    }
}

if (!function_exists('flash')) {
    function flash($key, $value = null) {
        return Session::flash($key, $value);
    }
}

/**
 * Database helpers
 */
if (!function_exists('db')) {
    function db($table = null) {
        if ($table === null) {
            return Database::class;
        }
        return Database::table($table);
    }
}

if (!function_exists('db_raw')) {
    function db_raw($sql, $params = []) {
        return Database::raw($sql, $params);
    }
}

/**
 * String helpers
 */
if (!function_exists('str_limit')) {
    function str_limit($string, $limit = 100, $end = '...') {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }
        return mb_substr($string, 0, $limit) . $end;
    }
}

if (!function_exists('str_slug')) {
    function str_slug($string) {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        return trim($string, '-');
    }
}

if (!function_exists('str_random')) {
    function str_random($length = 16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $string;
    }
}

/**
 * Array helpers
 */
if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null) {
        if (isset($array[$key])) {
            return $array[$key];
        }
        
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }
        
        return $array;
    }
}

if (!function_exists('array_only')) {
    function array_only($array, $keys) {
        return array_intersect_key($array, array_flip((array) $keys));
    }
}

if (!function_exists('array_except')) {
    function array_except($array, $keys) {
        return array_diff_key($array, array_flip((array) $keys));
    }
}

/**
 * URL helpers
 */
if (!function_exists('url')) {
    function url($path = '') {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
                   . "://" . $_SERVER['HTTP_HOST'];
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset($path) {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('current_url')) {
    function current_url() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
               . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}

/**
 * Validation helpers
 */
if (!function_exists('is_email')) {
    function is_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('is_url')) {
    function is_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

/**
 * Security helpers
 */
if (!function_exists('escape')) {
    function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    function e($string) {
        return escape($string);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        Session::start();
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
    }
}

/**
 * Date helpers
 */
if (!function_exists('now')) {
    function now($format = 'Y-m-d H:i:s') {
        return date($format);
    }
}

if (!function_exists('today')) {
    function today($format = 'Y-m-d') {
        return date($format);
    }
}

if (!function_exists('human_date')) {
    function human_date($date) {
        $timestamp = strtotime($date);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('M d, Y', $timestamp);
        }
    }
}

/**
 * Debugging helpers
 */
if (!function_exists('logger')) {
    function logger($message, $data = []) {
        $logFile = __DIR__ . '/../storage/logs/app.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $log = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        if (!empty($data)) {
            $log .= ' | Data: ' . json_encode($data);
        }
        $log .= PHP_EOL;
        
        file_put_contents($logFile, $log, FILE_APPEND);
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        
        // Convert string booleans
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    function config($key, $default = null) {
        $keys = explode('.', $key);
        $file = array_shift($keys);
        
        $configFile = __DIR__ . "/../app/config/{$file}.php";
        if (!file_exists($configFile)) {
            return $default;
        }
        
        $config = require $configFile;
        
        foreach ($keys as $segment) {
            if (is_array($config) && array_key_exists($segment, $config)) {
                $config = $config[$segment];
            } else {
                return $default;
            }
        }
        
        return $config;
    }
}

/**
 * File helpers
 */
if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        return __DIR__ . '/../storage/' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    function public_path($path = '') {
        return __DIR__ . '/../assets/' . ltrim($path, '/');
    }
}