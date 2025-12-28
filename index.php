<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Database;

// Load config
$dbConfig = require __DIR__ . '/app/config/database.php';
$appConfig = require __DIR__ . '/app/config/app.php';

// Set timezone
date_default_timezone_set($appConfig['timezone']);

// Initialize database config (but don't connect yet - lazy loading)
Database::init($dbConfig);

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
