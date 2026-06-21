<?php

require __DIR__ . '/../vendor/autoload.php';

use \App\Core\Router;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Only expose PHP errors when APP_DEBUG is explicitly enabled (keep off in production).
$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
error_reporting($debug ? E_ALL : 0);
ini_set('display_errors', $debug ? '1' : '0');

$router = new Router();
$router->dispatch();
