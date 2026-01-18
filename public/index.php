<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

use \App\Core\Router;

$router = new Router();
$router->dispatch();

?>