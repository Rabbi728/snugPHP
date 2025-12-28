<?php
// ==================== CORE FILES ====================

// File: core/Router.php
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
        $controllerClass = "App\\Controllers\\{$controller}";
        
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
        return call_user_func_array([$controllerInstance, $action], $params);
    }
}

// File: core/Request.php
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

// File: core/Response.php
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

// File: core/Database.php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct($config) {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
        
        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function connect($config) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    public static function table($table) {
        return new QueryBuilder(self::$instance->connection, $table);
    }
    
    public function raw($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

// File: core/QueryBuilder.php
namespace Core;

use PDO;

class QueryBuilder {
    private $pdo;
    private $table;
    private $wheres = [];
    private $bindings = [];
    private $selects = ['*'];
    private $joins = [];
    private $orderBy = [];
    private $groupBy = [];
    private $limit = null;
    private $offset = null;
    
    public function __construct(PDO $pdo, $table) {
        $this->pdo = $pdo;
        $this->table = $table;
    }
    
    public function select(...$columns) {
        $this->selects = $columns;
        return $this;
    }
    
    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'where',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        
        return $this;
    }
    
    public function orWhere($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'where',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];
        
        return $this;
    }
    
    public function whereIn($column, array $values) {
        $this->wheres[] = [
            'type' => 'whereIn',
            'column' => $column,
            'values' => $values,
            'boolean' => 'AND'
        ];
        
        return $this;
    }
    
    public function whereNull($column) {
        $this->wheres[] = [
            'type' => 'whereNull',
            'column' => $column,
            'boolean' => 'AND'
        ];
        
        return $this;
    }
    
    public function whereLike($column, $value) {
        $this->wheres[] = [
            'type' => 'where',
            'column' => $column,
            'operator' => 'LIKE',
            'value' => $value,
            'boolean' => 'AND'
        ];
        
        return $this;
    }
    
    public function join($table, $first, $operator, $second) {
        $this->joins[] = [
            'type' => 'INNER',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        
        return $this;
    }
    
    public function leftJoin($table, $first, $operator, $second) {
        $this->joins[] = [
            'type' => 'LEFT',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        
        return $this;
    }
    
    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = "$column $direction";
        return $this;
    }
    
    public function groupBy(...$columns) {
        $this->groupBy = array_merge($this->groupBy, $columns);
        return $this;
    }
    
    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }
    
    public function get() {
        $sql = $this->buildSelectQuery();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }
    
    public function first() {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }
    
    public function find($id) {
        return $this->where('id', $id)->first();
    }
    
    public function paginate($perPage = 15, $page = 1) {
        $total = $this->count();
        $offset = ($page - 1) * $perPage;
        
        $items = $this->limit($perPage)->offset($offset)->get();
        
        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    public function insert(array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }
    
    public function update(array $data) {
        $set = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $set";
        
        $bindings = array_values($data);
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($bindings);
        }
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($bindings);
    }
    
    public function delete() {
        $sql = "DELETE FROM {$this->table}";
        
        $bindings = [];
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($bindings);
        }
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($bindings);
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($this->bindings);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetch()['count'];
    }
    
    private function buildSelectQuery() {
        $columns = implode(', ', $this->selects);
        $sql = "SELECT $columns FROM {$this->table}";
        
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($this->bindings);
        }
        
        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }
        
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        return $sql;
    }
    
    private function buildWhereClause(&$bindings) {
        $conditions = [];
        
        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? '' : $where['boolean'] . ' ';
            
            if ($where['type'] === 'where') {
                $conditions[] = "$boolean{$where['column']} {$where['operator']} ?";
                $bindings[] = $where['value'];
            } elseif ($where['type'] === 'whereIn') {
                $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                $conditions[] = "$boolean{$where['column']} IN ($placeholders)";
                $bindings = array_merge($bindings, $where['values']);
            } elseif ($where['type'] === 'whereNull') {
                $conditions[] = "$boolean{$where['column']} IS NULL";
            }
        }
        
        return implode(' ', $conditions);
    }
}

// File: core/View.php
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

// File: core/Middleware.php
namespace Core;

abstract class Middleware {
    abstract public function handle();
}

// File: core/Session.php
namespace Core;

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy() {
        self::start();
        session_destroy();
    }
    
    public static function flash($key, $value = null) {
        self::start();
        
        if ($value === null) {
            $value = self::get($key);
            self::remove($key);
            return $value;
        }
        
        self::set($key, $value);
    }
}

// ==================== APP FILES ====================

// File: app/controllers/HomeController.php
namespace App\Controllers;

use Core\View;

class HomeController {
    public function index() {
        View::render('home', [
            'title' => 'Welcome to Auto Routing'
        ]);
    }
    
    public function about() {
        View::render('about');
    }
}

// File: app/controllers/UserController.php
namespace App\Controllers;

use Core\View;
use Core\Database;
use Core\Request;
use Core\Response;

class UserController {
    public function index() {
        $users = Database::table('users')
            ->orderBy('created_at', 'DESC')
            ->get();
        
        View::render('users/index', ['users' => $users]);
    }
    
    public function show($id) {
        $user = Database::table('users')->find($id);
        
        if (!$user) {
            View::render('errors/404');
            return;
        }
        
        View::render('users/show', ['user' => $user]);
    }
    
    public function create() {
        View::render('users/create');
    }
    
    public function store() {
        $request = new Request();
        $response = new Response();
        
        $data = [
            'name' => $request->post('name'),
            'email' => $request->post('email'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $id = Database::table('users')->insert($data);
        
        $response->redirect("/user/show/{$id}");
    }
    
    public function edit($id) {
        $user = Database::table('users')->find($id);
        View::render('users/edit', ['user' => $user]);
    }
    
    public function update($id) {
        $request = new Request();
        $response = new Response();
        
        $data = [
            'name' => $request->post('name'),
            'email' => $request->post('email')
        ];
        
        Database::table('users')->where('id', $id)->update($data);
        
        $response->redirect("/user/show/{$id}");
    }
    
    public function delete($id) {
        $response = new Response();
        
        Database::table('users')->where('id', $id)->delete();
        
        $response->redirect('/user/index');
    }
}

// File: app/controllers/ApiController.php
namespace App\Controllers;

use Core\Response;
use Core\Database;

class ApiController {
    public function users() {
        $response = new Response();
        $users = Database::table('users')->get();
        $response->json($users);
    }
    
    public function user($id) {
        $response = new Response();
        $user = Database::table('users')->find($id);
        
        if (!$user) {
            $response->json(['error' => 'User not found'], 404);
            return;
        }
        
        $response->json($user);
    }
}

// File: app/config/database.php
return [
    'host' => 'localhost',
    'database' => 'your_database',
    'username' => 'root',
    'password' => ''
];

// File: app/config/app.php
return [
    'name' => 'My PHP Framework',
    'url' => 'http://localhost',
    'timezone' => 'Asia/Dhaka',
    'debug' => true,
    'auto_routing' => true  // true = auto routing enabled, false = use route.php only
];

// File: app/route.php
use Core\Router;
use Core\View;
use Core\Database;
use Core\Request;
use Core\Response;
use App\Middlewares\AuthMiddleware;

$router = new Router();

/**
 * AUTO ROUTING SYSTEM
 * 
 * When auto_routing is enabled in config/app.php, you don't need to define routes here.
 * The system will automatically route based on URL pattern:
 * 
 * URL Pattern: /controller/method/param1/param2
 * 
 * Examples:
 * /user/show/123          -> UserController->show(123)
 * /home/about             -> HomeController->about()
 * /api/users              -> ApiController->users()
 * /                       -> HomeController->index()
 * 
 * Controllers should be in: app/controllers/
 * Controller naming: {Name}Controller.php
 * 
 * If you want manual routing, set 'auto_routing' => false in config/app.php
 * and define your routes below:
 */

// Manual routes (optional - only used if you need custom routing)

// Home page
$router->get('/', function() {
    View::render('home', [
        'title' => 'Welcome to My Framework'
    ]);
});

// About page
$router->get('/about', function() {
    View::render('about');
});

// Users list
$router->get('/users', function() {
    $users = Database::table('users')
        ->orderBy('created_at', 'DESC')
        ->get();
    
    View::render('users/index', ['users' => $users]);
});

// Single user
$router->get('/users/{id}', function($id) {
    $user = Database::table('users')->find($id);
    
    if (!$user) {
        View::render('errors/404');
        return;
    }
    
    View::render('users/show', ['user' => $user]);
});

// Create user form
$router->get('/users/create', function() {
    View::render('users/create');
});

// Store user
$router->post('/users', function() {
    $request = new Request();
    $response = new Response();
    
    $data = [
        'name' => $request->post('name'),
        'email' => $request->post('email'),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id = Database::table('users')->insert($data);
    
    $response->redirect("/users/{$id}");
});

// Update user
$router->put('/users/{id}', function($id) {
    $request = new Request();
    $response = new Response();
    
    $data = [
        'name' => $request->input('name'),
        'email' => $request->input('email')
    ];
    
    Database::table('users')->where('id', $id)->update($data);
    
    $response->json(['success' => true]);
});

// Delete user
$router->delete('/users/{id}', function($id) {
    $response = new Response();
    
    Database::table('users')->where('id', $id)->delete();
    
    $response->json(['success' => true]);
});

// Protected routes with middleware
$router->group([AuthMiddleware::class], function($router) {
    
    $router->get('/dashboard', function() {
        $user = Database::table('users')->find($_SESSION['user_id']);
        View::render('dashboard', ['user' => $user]);
    });
    
    $router->get('/profile', function() {
        View::render('profile');
    });
    
});

// API routes
$router->get('/api/users', function() {
    $response = new Response();
    $users = Database::table('users')->get();
    $response->json($users);
});

return $router;

// File: app/middlewares/AuthMiddleware.php
namespace App\Middlewares;

use Core\Middleware;
use Core\Response;
use Core\Session;

class AuthMiddleware extends Middleware {
    public function handle() {
        Session::start();
        
        if (!Session::has('user_id')) {
            $response = new Response();
            $response->redirect('/login');
            return false;
        }
        
        return true;
    }
}

// File: app/pages/home.php
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Home' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= $title ?? 'Welcome' ?></h1>
        <p>এটি একটি custom PHP framework এর home page।</p>
        <nav>
            <a href="/">Home</a>
            <a href="/about">About</a>
            <a href="/users">Users</a>
        </nav>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>

// File: app/pages/users/index.php
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>All Users</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>সব Users</h1>
        <a href="/users/create" class="btn">নতুন User যোগ করুন</a>
        
        <?php if (!empty($users)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <a href="/users/<?= $user['id'] ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>কোন user পাওয়া যায়নি।</p>
        <?php endif; ?>
    </div>
</body>
</html>

// File: app/pages/errors/404.php
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>404 - Not Found</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <p>দুঃখিত, আপনি যে page টি খুঁজছেন তা পাওয়া যায়নি।</p>
        <a href="/">Home এ ফিরে যান</a>
    </div>
</body>
</html>

// File: index.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Database;

// Load config
$dbConfig = require __DIR__ . '/app/config/database.php';
$appConfig = require __DIR__ . '/app/config/app.php';

// Set timezone
date_default_timezone_set($appConfig['timezone']);

// Initialize database
Database::connect($dbConfig);

// Load routes
$router = require __DIR__ . '/app/route.php';

// Enable/Disable auto routing based on config
if ($appConfig['auto_routing'] === true) {
    $router->enableAutoRouting();
} else {
    $router->disableAutoRouting();
}

// Resolve current request
$router->resolve();

// File: .htaccess
RewriteEngine On

# Serve static files directly
RewriteCond %{REQUEST_URI} ^/assets/.*
RewriteRule ^ - [L]

# Redirect all other requests to index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]

# Disable directory browsing
Options -Indexes

// File: assets/css/style.css
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    background: #f4f4f4;
    color: #333;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

h1 {
    color: #2c3e50;
    margin-bottom: 20px;
}

nav {
    background: #2c3e50;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

nav a {
    color: white;
    text-decoration: none;
    margin-right: 20px;
    padding: 5px 10px;
    border-radius: 3px;
    transition: background 0.3s;
}

nav a:hover {
    background: #34495e;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    margin-top: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background: #2c3e50;
    color: white;
}

tr:hover {
    background: #f5f5f5;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #3498db;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-bottom: 20px;
    transition: background 0.3s;
}

.btn:hover {
    background: #2980b9;
}

// File: assets/js/app.js
console.log('Framework loaded successfully!');

// Example: Simple form validation
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready');
});

// File: composer.json
{
    "name": "your-name/php-framework",
    "description": "A simple PHP MVC framework",
    "type": "project",
    "autoload": {
        "psr-4": {
            "Core\\": "core/",
            "App\\": "app/"
        }
    },
    "require": {
        "php": ">=7.4"
    }
}