<?php

use Core\Router;
use Core\View;
use Core\Database;
use Core\Request;
use Core\Response;
use App\Middlewares\AuthMiddleware;

$router = new Router();

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