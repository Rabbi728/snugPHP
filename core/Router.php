<?php

namespace Core;

class Router {
    private $routes = [];
    private $middlewares = [];
    private $groupMiddlewares = [];
    private $autoRouting = false;
    
    public function enableAutoRouting() {
        $this->autoRouting = true;
    }
    
    public function disableAutoRouting() {
        $this->autoRouting = false;
    }
    
    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
        return $this;
    }
    
    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
        return $this;
    }
    
    public function put($path, $callback) {
        $this->addRoute('PUT', $path, $callback);
        return $this;
    }
    
    public function delete($path, $callback) {
        $this->addRoute('DELETE', $path, $callback);
        return $this;
    }
    
    private function addRoute($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'middlewares' => $this->groupMiddlewares
        ];
    }
    
    public function middleware($middleware) {
        if (empty($this->routes)) {
            $this->groupMiddlewares[] = $middleware;
        } else {
            $lastRoute = count($this->routes) - 1;
            $this->routes[$lastRoute]['middlewares'][] = $middleware;
        }
        return $this;
    }
    
    public function group($middlewares, $callback) {
        $previousMiddlewares = $this->groupMiddlewares;
        $this->groupMiddlewares = array_merge($this->groupMiddlewares, (array)$middlewares);
        
        $callback($this);
        
        $this->groupMiddlewares = $previousMiddlewares;
    }
    
    public function resolve() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Try manual routes first
        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Execute middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = new $middleware();
                    $result = $middlewareInstance->handle();
                    if ($result === false) {
                        return;
                    }
                }
                
                if (is_callable($route['callback'])) {
                    return call_user_func_array($route['callback'], $params);
                }
                
                if (is_array($route['callback'])) {
                    [$controller, $method] = $route['callback'];
                    $controllerInstance = new $controller();
                    return call_user_func_array([$controllerInstance, $method], $params);
                }
            }
        }
        
        // Try auto routing if enabled
        if ($this->autoRouting) {
            $result = $this->autoRoute($uri, $method);
            if ($result !== null) {
                return $result;
            }
        }
        
        http_response_code(404);
        View::render('errors/404');
    }
    
    private function autoRoute($uri, $method) {
        // Remove leading/trailing slashes
        $uri = trim($uri, '/');
        
        // Split URI into segments
        $segments = $uri ? explode('/', $uri) : [];
        
        // Default to home if no segments
        if (empty($segments)) {
            $controller = 'HomeController';
            $action = 'index';
            $params = [];
        } else {
            // First segment is controller
            $controller = ucfirst($segments[0]) . 'Controller';
            
            // Second segment is action (method), default to index
            $action = isset($segments[1]) ? $segments[1] : 'index';
            
            // Rest are parameters
            $params = array_slice($segments, 2);
        }
        
        // Build controller class name
        $controllerClass = "App\\controllers\\{$controller}";
        
        // Check if controller exists
        if (!class_exists($controllerClass)) {
            return null;
        }
        
        $controllerInstance = new $controllerClass();
        
        // Check if method exists
        if (!method_exists($controllerInstance, $action)) {
            return null;
        }
        
        // Call the controller method with params
        call_user_func_array([$controllerInstance, $action], $params);
        return true;
    }
}