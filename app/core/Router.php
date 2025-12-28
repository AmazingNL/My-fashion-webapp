<?php

declare(strict_types=1);

namespace App\Core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    public function dispatch(): void
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            //$r->addRoute('GET', '/', ['App\Controllers\UserController', 'showRegistrationForm']);
            $r->addRoute('GET', '/viewUsers', ['App\Controllers\UserController', 'viewUsers']);
            $r->addRoute('POST', '/registerUser', ['App\Controllers\UserController', 'registerUser']);
            $r->addRoute('GET', '/', ['App\Controllers\AuthController', 'showLogin']);
            $r->addRoute('POST', '/login', ['App\Controllers\AuthController', 'login']);
            $r->addRoute('POST', '/logout', ['App\Controllers\AuthController', 'logout']);
            //$r->addRoute('GET', '/', ['App\Controllers\ProductController', 'addProductForm']);
            $r->addRoute('POST', '/addProduct', ['App\Controllers\ProductController', 'addProduct']);
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (false !== $pos = strpos($uri, '?'))
            $uri = substr($uri, 0, $pos);
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo "404 - Page not found";
                return;

            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo "405 - Method not allowed";
                return;

            case Dispatcher::FOUND:
                [$class, $method] = $routeInfo[1];
                $vars = $routeInfo[2];

                if (!class_exists($class)) {
                    http_response_code(500);
                    echo "Controller not found: " . htmlspecialchars($class);
                    return;
                }

                // Build controller (inject dependencies when needed)
                switch ($class) {
                    case (\App\Controllers\UserController::class || \App\Controllers\AuthController::class):
                        $userRepository = new \App\Repositories\UserRepository();
                        $userService = new \App\Services\UserService($userRepository);
                        $controller = new $class($userService);
                        break;

                    case \App\Controllers\ProductController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $productService = new \App\Services\ProductService($productRepository);
                        $controller = new $class($productService);
                        break;

                    default:
                        $controller = new $class();
                        break;
                }

                if (!method_exists($controller, $method)) {
                    http_response_code(500);
                    echo "Method not found: " . htmlspecialchars($class . '::' . $method);
                    return;
                }

                call_user_func_array([$controller, $method], $vars);
                return;

        }
    }
}
