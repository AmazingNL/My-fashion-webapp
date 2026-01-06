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
            $r->addRoute('GET', '/', ['App\Controllers\AuthController', 'showLogin']);
            $r->addRoute('POST', '/login', ['App\Controllers\AuthController', 'login']);
            $r->addRoute('GET', '/showRegistrationForm', ['App\Controllers\UserController', 'showRegistrationForm']);
            $r->addRoute('GET', '/viewUsers', ['App\Controllers\UserController', 'viewUsers']);
            $r->addRoute('POST', '/registerUser', ['App\Controllers\UserController', 'registerUser']);
            $r->addRoute('POST', '/logout', ['App\Controllers\AuthController', 'logout']);


            /// Product routes
            $r->addRoute('GET', '/productLists', ['App\Controllers\ProductController', 'productLists']);
            $r->addRoute('GET', '/products', ['App\Controllers\ProductController', 'products']);
            $r->addRoute('GET', '/products/{id}', ['App\Controllers\ProductController', 'productDetails']);
            $r->addRoute('GET', '/api/products/{id}', ['App\Controllers\ProductController', 'viewProductDetail']);


            // Cart routes
            $r->addRoute('GET', '/viewCart', ['App\Controllers\CartController', 'viewCart']);
            $r->addRoute('POST', '/addToBasket', ['App\Controllers\CartController', 'addToBasket']);
            $r->addRoute('POST', '/updateQuantity', ['App\Controllers\CartController', 'updateQuantity']);
            $r->addRoute('POST', '/removeFromBasket', ['App\Controllers\CartController', 'removeFromBasket']);
            $r->addRoute('POST', '/clearBasket', ['App\Controllers\CartController', 'clearBasket']);
            $r->addRoute('GET', '/getBasketCount', ['App\Controllers\CartController', 'getBasketCount']);


            // Favourite routes
            $r->addRoute('GET', '/favourites', ['App\Controllers\FavouriteController', 'viewFavourites']);
            $r->addRoute('GET', '/api/favourites/list', ['App\Controllers\FavouriteController', 'getFavouriteList']);
            $r->addRoute('GET', '/api/favourites/products', ['App\Controllers\FavouriteController', 'getFavouriteProducts']);
            $r->addRoute('GET', '/api/favourites/count', ['App\Controllers\FavouriteController', 'getFavouriteCount']);
            $r->addRoute('POST', '/api/favourites/toggle', ['App\Controllers\FavouriteController', 'toggleFavourite']);
            $r->addRoute('POST', '/api/favourites/clear', ['App\Controllers\FavouriteController', 'clearFavourites']);

            // Admin routes
            $r->addRoute('GET', '/admin/dashboard', ['App\Controllers\AdminController', 'dashboard']);
            $r->addRoute('GET', '/admin/users', ['App\Controllers\AdminController', 'manageUsers']);
            $r->addRoute('GET', '/admin/products', ['App\Controllers\AdminController', 'manageProducts']);
            $r->addRoute('GET', '/admin/addProductForm', ['App\Controllers\AdminController', 'addProductForm']);
            $r->addRoute('POST', '/admin/addProduct', ['App\Controllers\AdminController', 'addProduct']);
            $r->addRoute('GET', '/admin/activity', ['App\Controllers\AdminController', 'viewLogs']);
            $r->addRoute('GET', '/admin/activity-logs', ['App\Controllers\ActivityLogController', 'adminIndex']);
            $r->addRoute('GET', '/admin/activity-logs/api', ['App\Controllers\ActivityLogController', 'apiList']);
            $r->addRoute('POST', '/admin/activity-logs/export', ['App\Controllers\ActivityLogController', 'export']);
            $r->addRoute('POST', '/admin/activity-logs/purge', ['App\Controllers\ActivityLogController', 'purge']);
            $r->addRoute('POST', '/admin/activity-logs/clear', ['App\Controllers\ActivityLogController', 'clear']);
            $r->addRoute('GET', '/admin/orders', ['App\Controllers\AdminController', 'manageOrders']);
            $r->addRoute('POST', '/admin/users/status', ['App\Controllers\AdminController', 'updateUserStatus']);
            $r->addRoute('POST', '/admin/products/delete', ['App\Controllers\AdminController', 'deleteProduct']);
            $r->addRoute('GET', '/admin/products/edit/{id}', ['App\Controllers\AdminController', 'editProductForm']);
            $r->addRoute('POST', '/admin/products/update', ['App\Controllers\AdminController', 'updateProduct']);
            $r->addRoute('GET', '/admin/orders/api', ['App\Controllers\OrderController', 'adminApiList']);
            $r->addRoute('GET', '/admin/orders/{id:\d+}/items/api', ['App\Controllers\OrderController', 'adminApiItems']);


            // Orders + Checkout
            $r->addRoute('GET', '/orders', ['App\Controllers\OrderController', 'index']);
            $r->addRoute('GET', '/orders/{id:\d+}', ['App\Controllers\OrderController', 'show']);
            $r->addRoute('POST', '/orders/{id:\d+}/cancel', ['App\Controllers\OrderController', 'cancel']);
            $r->addRoute('GET', '/checkout', ['App\Controllers\CheckoutController', 'showCheckout']);
            $r->addRoute('POST', '/checkout/place', ['App\Controllers\CheckoutController', 'processCheckout']);
            $r->addRoute('GET', '/checkout/confirmation/{id:\d+}', ['App\Controllers\CheckoutController', 'confirmation']);
            // Orders API
            $r->addRoute('GET', '/api/orders', ['App\Controllers\OrderController', 'apiList']);
            $r->addRoute('GET', '/api/orders/{id:\d+}', ['App\Controllers\OrderController', 'apiShow']);
            $r->addRoute('GET', '/api/orders/{id:\d+}/items', ['App\Controllers\OrderController', 'apiItems']);


            // Customer appointment routes
            $r->addRoute('GET', '/appointments', ['App\Controllers\AppointmentController', 'index']);
            $r->addRoute('GET', '/appointments/book', ['App\Controllers\AppointmentController', 'bookForm']);
            $r->addRoute('POST', '/appointments/book', ['App\Controllers\AppointmentController', 'book']);
            $r->addRoute('GET', '/appointments/{id:\d+}/edit', ['App\Controllers\AppointmentController', 'editForm']);
            $r->addRoute('POST', '/appointments/{id:\d+}/slot', ['App\Controllers\AppointmentController', 'updateSlot']);
            $r->addRoute('POST', '/appointments/{id:\d+}/save', ['App\Controllers\AppointmentController', 'updateDetails']);
            $r->addRoute('POST', '/appointments/{id:\d+}/cancel', ['App\Controllers\AppointmentController', 'cancel']);
            // API for slots
            $r->addRoute('GET', '/api/appointments/slots', ['App\Controllers\AppointmentController', 'apiAvailableSlots']);
            // Admin appointment routes
            $r->addRoute('GET', '/admin/appointments', ['App\Controllers\AppointmentController', 'adminIndex']);
            $r->addRoute('POST', '/admin/appointments/slots/add', ['App\Controllers\AppointmentController', 'adminAddSlot']);
            $r->addRoute('POST', '/admin/appointments/{id:\d+}/status', ['App\Controllers\AppointmentController', 'adminSetStatus']);


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
                    case \App\Controllers\UserController::class:
                    case \App\Controllers\AuthController::class:
                        $userRepository = new \App\Repositories\UserRepository();
                        $activityLogRepository = new \App\Repositories\ActivityLogRepository();
                        $userService = new \App\Services\UserService($userRepository);
                        $activityLogService = new \App\Services\ActivityLogService($activityLogRepository);
                        $controller = new $class($userService, $activityLogService);
                        break;

                    case \App\Controllers\ProductController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $productService = new \App\Services\ProductService($productRepository);
                        $controller = new $class($productService);
                        break;

                    case \App\Controllers\CartController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $activityLogRepository = new \App\Repositories\ActivityLogRepository();
                        $cartService = new \App\Services\CartService($productRepository);
                        $activityLogService = new \App\services\ActivityLogService($activityLogRepository);
                        $controller = new $class($cartService, $activityLogService);
                        break;

                    case \App\Controllers\CheckoutController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $cartService = new \App\Services\CartService($productRepository);

                        $orderRepository = new \App\Repositories\OrderRepository();
                        $orderItemRepository = new \App\Repositories\OrderItemRepository();
                        $orderItemService = new \App\Services\OrderItemService($orderItemRepository);
                        $orderService = new \App\Services\OrderService($orderRepository, $orderItemService, $cartService);

                        $activityLogRepository = new \App\Repositories\ActivityLogRepository();
                        $activityLogService = new \App\Services\ActivityLogService($activityLogRepository);

                        $controller = new $class($cartService, $orderService, $orderItemService, $activityLogService);
                        break;


                    case \App\Controllers\FavouriteController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $activityLogRepository = new \App\Repositories\ActivityLogRepository();
                        $productService = new \App\Services\ProductService($productRepository);
                        $activityLogService = new \App\services\ActivityLogService($activityLogRepository);
                        $controller = new $class($productService, $activityLogService);
                        break;

                    case \App\Controllers\OrderController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $orderRepository = new \App\Repositories\OrderRepository();
                        $orderItemRepository = new \App\Repositories\OrderItemRepository();
                        $orderItemService = new \App\Services\OrderItemService($orderItemRepository);
                        $cartService = new \App\Services\CartService($productRepository);
                        $orderService = new \App\Services\OrderService($orderRepository, $orderItemService, $cartService);
                        $controller = new $class($orderService, $cartService, $orderItemService);
                        break;


                    case \App\Controllers\AppointmentController::class:
                        $appointmentRepo = new \App\Repositories\AppointmentRepository();
                        $slotRepo = new \App\Repositories\AppointmentSlotRepository();
                        $service = new \App\Services\AppointmentService($appointmentRepo, $slotRepo);
                        $controller = new $class($service);
                        break;


                    case \App\Controllers\AdminController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $userRepository = new \App\Repositories\UserRepository();
                        $activityLogRepository = new \App\Repositories\ActivityLogRepository();
                        $productService = new \App\Services\ProductService($productRepository);
                        $userService = new \App\Services\UserService($userRepository);
                        $activityLogService = new \App\Services\ActivityLogService($activityLogRepository);
                        $controller = new $class($productService, $userService, $activityLogService);
                        break;

                    case \App\Controllers\ActivityLogController::class:
                        $activityLogRepository = new \App\Repositories\ActivityLogRepository();
                        $activityLogService = new \App\Services\ActivityLogService($activityLogRepository);
                        $controller = new $class($activityLogService);
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

                // Ensure session exists
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }

                // Protect all admin routes
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
                if (str_starts_with($path, '/admin')) {
                    \App\Core\Middleware::requireAdmin();
                }


                call_user_func_array([$controller, $method], array_values($vars));
                return;

        }
    }
}
