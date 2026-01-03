# snugPHP Starter

> A lightweight, fast, and elegant PHP framework starter kit for modern web development.

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 🚀 Overview

**snugPHP Starter** is the boilerplate for creating applications with the **snugPHP** framework. It provides the essential directory structure and configuration to get you started immediately.

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
├── .env                   # Environment variables
├── composer.json          # Dependencies & autoloading
│
├── app/                   # Your application code
│   ├── config/            # Configuration files
│   │   ├── database.php   # Database configuration
│   │   └── app.php        # App settings
│   ├── controllers/       # Controller classes
│   │   ├── HomeController.php
│   │   └── ...
│   ├── middlewares/       # Middleware classes
│   │   └── AuthMiddleware.php
│   ├── views/             # Views & Layouts
│   │   ├── layouts/       # Master layouts
│   │   └── pages/         # Page templates
│   │       ├── home.php
│   │       └── ...
│   └── route.php          # Manual route definitions
│
├── assets/                # Static files
│   ├── css/
│   ├── js/
│   └── images/
│
└── vendor/                # Composer dependencies (includes framework core)
```

---

## 🔧 Installation

To create a new project using snugPHP:

```bash
composer create-project snugphp/starter my-project
```

### Requirements

- PHP >= 7.4
- MySQL/MariaDB
- Apache/Nginx with mod_rewrite enabled
- Composer

### Quick Start

1.  **Navigate to your project directory**:

    ```bash
    cd my-project
    ```

2.  **Configure Environment**:
    
    The installation should have created a `.env` file from `.env.example`. If not, copy it manually:
    
    ```bash
    cp .env.example .env
    ```
    
    Update `.env` with your database credentials.

3.  **Start development server**:

    ```bash
    php -S localhost:8000
    ```

    Visit `http://localhost:8000` in your browser.

---

## 🎯 Routing

snugPHP supports both **manual routing** and **automatic routing**.

### Auto Routing

When `APP_AUTO_ROUTING` is enabled in `.env` (or config), URLs automatically map to controllers:

**URL Pattern:** `/controller/method/param1/param2`

```
/                       → HomeController->index()
/user/index             → UserController->index()
/user/show/123          → UserController->show(123)
/user/delete/123        → UserController->delete(123)
```

### Manual Routing

Define custom routes in `app/route.php`:

```php
use Core\Router;

$router = new Router();

$router->get('/', function() {
    view('home');
});

$router->get('/user/index', [UserController::class, 'index']);
$router->post('/user/store', [UserController::class, 'store']);
$router->delete('/user/delete/{id}', [UserController::class, 'delete']);

return $router;
```

---

## 🎮 Controllers

Create controllers in `app/controllers/`.

```php
namespace App\Controllers;

class UserController {
    public function index() {
        $users = db('users')->get();
        view('users/index', ['users' => $users]);
    }

    public function store() {
        $user = db('users')->insert([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);
    }

    public function delete($id) {
        db('users')->where('id', $id)->delete();
    }   
}
```

---

## 🗄️ Database

Access the database using the global `db()` helper.

```php
// Select
$users = db('users')->where('status', 'active')->get();

// Insert
$id = db('users')->insert(['name' => 'John', 'email' => 'john@example.com']);

// Update
db('users')->where('id', 1)->update(['name' => 'Jane']);

// Delete
db('users')->where('id', 1)->delete();
```

---

## 👁️ Views

Views are located in `app/views/pages/` and layouts in `app/views/layouts/`.

```php
// Render 'app/views/pages/home.php'
view('home', ['title' => 'Welcome']);
```

---

## ❓ Help

For more detailed documentation, check the core framework repository or the helper functions available in `vendor/snugphp/framework`.

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

- **Documentation:** https://github.com/Rabbi728/snugPHP/blob/master/readme.md
- **Issues:** https://github.com/Rabbi728/snugPHP/issues
- **Email:** rabbiahamed0728@gmail.com

---

## 🎉 Credits

Created with ❤️ by the snugPHP Team

**Built for developers who value:**
- Speed over complexity
- Simplicity over bloat
- Productivity over configuration

---

**Happy coding with snugPHP! 🚀**