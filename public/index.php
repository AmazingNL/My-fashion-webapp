<?php

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteController;
use function FastRoute\simpleDispatcher;

$dispatcher = simpleDispatcher(function (RouteController $routerController))
    $routerController('GET', '/', ['App\Controllers\'])