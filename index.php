<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Database;

$dbConfig = require __DIR__ . '/app/config/database.php';
$appConfig = require __DIR__ . '/app/config/app.php';

date_default_timezone_set($appConfig['timezone']);

Database::connect($dbConfig);

$router = require __DIR__ . '/app/route.php';

$router->resolve();
