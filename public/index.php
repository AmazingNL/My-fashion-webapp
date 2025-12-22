<?php

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteController;
use function FastRoute\simpleDispatcher;

$dispatcher = simpileDispatcher(function (RouteController $routerController))
    $routerController('GET', '/', ['App\Controllers\'])