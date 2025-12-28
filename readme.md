# snugPHP Framework

> A lightweight, fast, and elegant PHP framework for modern web development

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 🚀 Overview

**snugPHP** is a modern, lightweight PHP framework designed for speed and simplicity. It combines the power of MVC architecture with automatic routing, a powerful query builder, and extensive helper functions to make PHP development faster and more enjoyable.

### Why snugPHP?

- ⚡ **Lightning Fast** - Minimal overhead, maximum performance
- 🎯 **Auto Routing** - Convention over configuration
- 🔧 **Query Builder** - Elegant database operations without models
- 🛠️ **Rich Helpers** - 50+ helper functions for common tasks
- 🔌 **Middleware Support** - Secure and modular request handling
- 📦 **Zero Bloat** - Only what you need, nothing more

---

## 📁 Directory Structure

```
your-project/
├── index.php              # Application entry point
├── .htaccess              # URL rewriting rules
├── composer.json          # Dependencies & autoloading
│
├── app/                   # Your application code
│   ├── config/            # Configuration files
│   │   ├── database.php   # Database configuration
│   │   └── app.php        # App settings
│   ├── controllers/       # Controller classes
│   │   ├── HomeController.php
│   │   ├── UserController.php
│   │   └── ApiController.php
│   ├── middlewares/       # Middleware classes
│   │   └── AuthMiddleware.php
│   ├── pages/             # View templates
│   │   ├── home.php
│   │   ├── users/
│   │   │   ├── index.php
│   │   │   ├── show.php
│   │   │   └── create.php
│   │   └── errors/
│   │       └── 404.php
│   └── route.php          # Manual route definitions (optional)
│
├── core/                  # Framework core (don't modify)
│   ├── Router.php
│   ├── Database.php
│   ├── QueryBuilder.php
│   ├── Request.php
│   ├── Response.php
│   ├── View.php
│   ├── Middleware.php
│   ├── Session.php
│   └── helpers.php
│
├── assets/                # Static files
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   └── images/
│
└── storage/               # Application storage
    └── logs/
        └── app.log
```

---

## 🔧 Installation

### Requirements

- PHP >= 7.4
- MySQL/MariaDB
- Apache/Nginx with mod_rewrite enabled
- Composer

### Quick Start

1. **Clone or download the framework**

```bash
git clone https://github.com/yourusername/snugPHP.git
cd snugPHP
```

2. **Install dependencies**

```bash
composer install
```

3. **Configure database**

Edit `app/config/database.php`:

```php
return [
    'host' => 'localhost',
    'database' => 'your_database',
    'username' => 'root',
    'password' => ''
];
```

4. **Configure application**

Edit `app/config/app.php`:

```php
return [
    'name' => 'My Application',
    'url' => 'http://localhost',
    'timezone' => 'Asia/Dhaka',
    'debug' => true,
    'auto_routing' => true  // Enable/disable auto routing
];
```

5. **Start development server**

```bash
php -S localhost:8000
```

Visit `http://localhost:8000` in your browser.

---

## 🎯 Routing

snugPHP supports both **manual routing** and **automatic routing**.

### Auto Routing

When `auto_routing` is enabled in config, URLs automatically map to controllers:

**URL Pattern:** `/controller/method/param1/param2`

```
/                       → HomeController->index()
/user/index             → UserController->index()
/user/show/123          → UserController->show(123)
/api/users              → ApiController->users()
/product/edit/5         → ProductController->edit(5)
```

**Controller naming convention:**
- File: `app/controllers/UserController.php`
- Class: `UserController`
- Namespace: `App\Controllers`

### Manual Routing

Define custom routes in `app/route.php`:

```php
use Core\Router;

$router = new Router();

// GET route
$router->get('/', function() {
    view('home');
});

// Route with parameters
$router->get('/users/{id}', function($id) {
    $user = db('users')->find($id);
    view('users/show', ['user' => $user]);
});

// POST route
$router->post('/users', function() {
    $data = request()->post();
    $id = db('users')->insert($data);
    redirect("/users/{$id}");
});

// PUT route
$router->put('/users/{id}', function($id) {
    db('users')->where('id', $id)->update(request()->post());
    json(['success' => true]);
});

// DELETE route
$router->delete('/users/{id}', function($id) {
    db('users')->where('id', $id)->delete();
    json(['success' => true]);
});

return $router;
```

### Controller Routes

```php
use App\Controllers\UserController;

$router->get('/users', [UserController::class, 'index']);
$router->get('/users/{id}', [UserController::class, 'show']);
$router->post('/users', [UserController::class, 'store']);
```

### Route Middleware

```php
use App\Middlewares\AuthMiddleware;

// Single route with middleware
$router->get('/dashboard', function() {
    view('dashboard');
})->middleware(AuthMiddleware::class);

// Route group with middleware
$router->group([AuthMiddleware::class], function($router) {
    $router->get('/admin', function() {
        view('admin/index');
    });
    
    $router->get('/profile', function() {
        view('profile');
    });
});
```

---

## 🎮 Controllers

Controllers handle application logic and responses.

### Creating a Controller

**File:** `app/controllers/UserController.php`

```php
<?php
namespace App\Controllers;

class UserController {
    
    public function index() {
        $users = db('users')->get();
        view('users/index', ['users' => $users]);
    }
    
    public function show($id) {
        $user = db('users')->find($id);
        
        if (!$user) {
            view('errors/404');
            return;
        }
        
        view('users/show', ['user' => $user]);
    }
    
    public function create() {
        view('users/create');
    }
    
    public function store() {
        $data = [
            'name' => escape(request('name')),
            'email' => request('email'),
            'created_at' => now()
        ];
        
        $id = db('users')->insert($data);
        
        flash('success', 'User created successfully!');
        redirect("/user/show/{$id}");
    }
    
    public function update($id) {
        db('users')->where('id', $id)->update([
            'name' => request('name'),
            'email' => request('email')
        ]);
        
        json(['success' => true]);
    }
    
    public function delete($id) {
        db('users')->where('id', $id)->delete();
        redirect('/user/index');
    }
}
```

---

## 🗄️ Database & Query Builder

snugPHP uses a powerful query builder for database operations.

### Basic Queries

```php
// Select all
$users = db('users')->get();

// Select specific columns
$users = db('users')->select('name', 'email')->get();

// Find by ID
$user = db('users')->find(1);

// Get first record
$user = db('users')->where('email', 'john@example.com')->first();

// Count records
$count = db('users')->count();
```

### Where Clauses

```php
// Simple where
db('users')->where('status', 'active')->get();

// Where with operator
db('users')->where('age', '>', 18)->get();

// Multiple conditions
db('users')
    ->where('status', 'active')
    ->where('age', '>', 18)
    ->get();

// OR where
db('users')
    ->where('role', 'admin')
    ->orWhere('role', 'moderator')
    ->get();

// Where IN
db('users')->whereIn('id', [1, 2, 3])->get();

// Where NULL
db('users')->whereNull('deleted_at')->get();

// Where LIKE
db('users')->whereLike('name', '%john%')->get();
```

### Joins

```php
db('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.name', 'posts.title')
    ->get();

db('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

### Ordering & Limiting

```php
// Order by
db('users')->orderBy('created_at', 'DESC')->get();

// Limit
db('users')->limit(10)->get();

// Offset
db('users')->offset(20)->limit(10)->get();

// Group by
db('orders')
    ->select('user_id', 'COUNT(*) as total')
    ->groupBy('user_id')
    ->get();
```

### Insert

```php
$id = db('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => now()
]);
```

### Update

```php
db('users')
    ->where('id', 1)
    ->update(['name' => 'Jane Doe']);
```

### Delete

```php
db('users')->where('id', 1)->delete();
```

### Pagination

```php
$result = db('users')->paginate(15, 1);

// Returns:
// [
//     'data' => [...],
//     'total' => 100,
//     'per_page' => 15,
//     'current_page' => 1,
//     'last_page' => 7
// ]
```

### Raw Queries

```php
$users = db_raw('SELECT * FROM users WHERE age > ?', [18])->fetchAll();
```

---

## 👁️ Views

Views are PHP templates located in `app/pages/`.

### Rendering Views

```php
// In controller or route
view('home');

// With data
view('users/show', [
    'user' => $user,
    'title' => 'User Profile'
]);
```

### Example View

**File:** `app/pages/users/show.php`

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'User Profile' ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <div class="container">
        <h1><?= e($user['name']) ?></h1>
        <p>Email: <?= e($user['email']) ?></p>
        <p>Joined: <?= human_date($user['created_at']) ?></p>
        
        <?php if (session_has('success')): ?>
            <div class="alert alert-success">
                <?= flash('success') ?>
            </div>
        <?php endif; ?>
        
        <a href="<?= url('/user/edit/' . $user['id']) ?>">Edit</a>
    </div>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
```

---

## 🔐 Middleware

Middleware filters HTTP requests entering your application.

### Creating Middleware

**File:** `app/middlewares/AuthMiddleware.php`

```php
<?php
namespace App\Middlewares;

use Core\Middleware;

class AuthMiddleware extends Middleware {
    
    public function handle() {
        session_start();
        
        if (!session_has('user_id')) {
            redirect('/login');
            return false;  // Stop execution
        }
        
        return true;  // Continue
    }
}
```

### Using Middleware

```php
// Single route
$router->get('/dashboard', function() {
    view('dashboard');
})->middleware(AuthMiddleware::class);

// Route group
$router->group([AuthMiddleware::class], function($router) {
    $router->get('/admin', function() {
        view('admin');
    });
});
```

---

## 🛠️ Helper Functions

snugPHP includes 50+ helper functions for common tasks.

### Debugging

```php
dd($variable);              // Dump and die
dump($variable);            // Dump without stopping
logger('message', $data);   // Log to file
```

### Request & Response

```php
request('name');            // Get input
request('name', 'default'); // With default value
request()->all();           // All inputs
request()->json();          // JSON input

redirect('/path');          // Redirect
json(['key' => 'value']);   // JSON response
view('page', $data);        // Render view
```

### Database

```php
db('users')->get();         // Query builder
db('users')->find(1);       // Find by ID
db_raw('SELECT ...', []);   // Raw query
```

### Session

```php
session('key');             // Get session
session_set('key', 'val');  // Set session
session_has('key');         // Check if exists
flash('msg', 'Success');    // Flash message
```

### String Helpers

```php
str_limit('text', 50);      // Limit length
str_slug('Hello World');    // Convert to slug
str_random(16);             // Random string
escape($text);              // HTML escape
e($text);                   // Short for escape()
```

### Array Helpers

```php
array_get($arr, 'key.nested', 'default');
array_only($arr, ['key1', 'key2']);
array_except($arr, ['key1']);
```

### URL Helpers

```php
url('/path');               // Generate full URL
asset('css/style.css');     // Asset URL
current_url();              // Current page URL
```

### Validation

```php
is_email('test@test.com');  // Validate email
is_url('https://...');      // Validate URL
```

### Security

```php
csrf_token();               // Generate CSRF token
csrf_field();               // CSRF hidden input
```

### Date & Time

```php
now();                      // Current datetime
today();                    // Current date
human_date('2024-01-01');   // Relative time
```

### Configuration

```php
config('app.name');         // Get config value
env('APP_ENV', 'local');    // Get environment variable
```

### Paths

```php
storage_path('logs/app.log');
public_path('uploads/');
```

---

## 📝 Form Handling

### Creating Forms

```php
<form action="<?= url('/user/store') ?>" method="POST">
    <?= csrf_field() ?>
    
    <input type="text" name="name" value="<?= request('name') ?>">
    <input type="email" name="email">
    
    <button type="submit">Submit</button>
</form>
```

### Processing Forms

```php
public function store() {
    // Validate
    if (empty(request('name')) || !is_email(request('email'))) {
        flash('error', 'Invalid input');
        return redirect('/user/create');
    }
    
    // Save
    $id = db('users')->insert([
        'name' => escape(request('name')),
        'email' => request('email'),
        'created_at' => now()
    ]);
    
    // Flash and redirect
    flash('success', 'User created!');
    redirect("/user/show/{$id}");
}
```

### File Uploads

```php
if (request()->hasFile('avatar')) {
    $file = request()->file('avatar');
    
    $filename = str_random(20) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $destination = storage_path('uploads/' . $filename);
    
    move_uploaded_file($file['tmp_name'], $destination);
    
    db('users')->where('id', $userId)->update([
        'avatar' => $filename
    ]);
}
```

---

## 🔒 Security

### CSRF Protection

```php
// In forms
<?= csrf_field() ?>

// Manual validation
if (request('_csrf_token') !== csrf_token()) {
    die('CSRF token mismatch');
}
```

### XSS Protection

```php
// Always escape user input in views
<?= e($user['name']) ?>
<?= escape($content) ?>
```

### SQL Injection Protection

Query builder automatically uses prepared statements:

```php
// Safe - uses prepared statements
db('users')->where('email', $email)->first();

// For raw queries, use parameters
db_raw('SELECT * FROM users WHERE email = ?', [$email]);
```

---

## 🔍 Error Handling

### 404 Pages

Create `app/pages/errors/404.php`:

```php
<!DOCTYPE html>
<html>
<head>
    <title>404 - Not Found</title>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you're looking for doesn't exist.</p>
    <a href="<?= url('/') ?>">Go Home</a>
</body>
</html>
```

### Logging

```php
logger('Error occurred', [
    'user_id' => $userId,
    'error' => $exception->getMessage()
]);
```

---

## 🚀 Deployment

### Production Configuration

1. **Set debug to false** in `app/config/app.php`:

```php
'debug' => false
```

2. **Configure .htaccess**:

```apache
RewriteEngine On

# Serve static files directly
RewriteCond %{REQUEST_URI} ^/assets/.*
RewriteRule ^ - [L]

# Redirect to index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]

Options -Indexes
```

3. **Set proper permissions**:

```bash
chmod 755 storage/
chmod 644 storage/logs/app.log
```

4. **Use environment variables** for sensitive data

5. **Enable HTTPS** in production

---

## 📚 Best Practices

### Controller Guidelines

- Keep controllers thin
- Use meaningful method names
- Return early for invalid input
- Use helper functions

### Database Guidelines

- Always use query builder or prepared statements
- Index frequently queried columns
- Use transactions for multiple operations
- Close connections when done

### Security Guidelines

- Validate all user input
- Escape output in views
- Use CSRF protection
- Never trust user input
- Keep framework updated

### Performance Tips

- Enable OPcache in production
- Use database indexing
- Minimize database queries
- Cache frequently accessed data
- Optimize images and assets

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write tests if applicable
5. Submit a pull request

---

## 📄 License

snugPHP is open-source software licensed under the [MIT license](LICENSE).

---

## 💬 Support

- **Documentation:** https://snugPHP.dev
- **Issues:** https://github.com/yourusername/snugPHP/issues
- **Email:** support@snugPHP.dev

---

## 🎉 Credits

Created with ❤️ by the snugPHP Team

**Built for developers who value:**
- Speed over complexity
- Simplicity over bloat
- Productivity over configuration

---

**Happy coding with snugPHP! 🚀**