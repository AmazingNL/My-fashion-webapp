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
            $r->get('/', ['App\Controllers\AuthController', 'showLogin']);
            $r->post('/login', ['App\Controllers\AuthController', 'login']);
            $r->get('/showRegistrationForm', ['App\Controllers\UserController', 'showRegistrationForm']);
            $r->get('/viewUsers', ['App\Controllers\UserController', 'viewUsers']);
            $r->post('/registerUser', ['App\Controllers\UserController', 'registerUser']);
            $r->post('/logout', ['App\Controllers\AuthController', 'logout']);
            $r->get('/aboutUs', ['App\Controllers\UserController', 'aboutUs']);
            // Forgot password
            $r->get('/forgotPassword', ['App\Controllers\AuthController', 'showForgotPassword']);
            $r->post('/forgotPassword', ['App\Controllers\AuthController', 'requestReset']);

            // Reset code verification page
            $r->get('/reset-password', ['App\Controllers\AuthController', 'showResetCode']);
            $r->post('/reset-password/verify', ['App\Controllers\AuthController', 'verifyResetCode']);



            /// Product routes
            $r->get('/productLists', ['App\Controllers\ProductController', 'productLists']);
            $r->get('/products/{id}', ['App\Controllers\ProductController', 'productDetails']);


            // Cart routes
            $r->get('/viewCart', ['App\Controllers\CartController', 'viewCart']);
            $r->post('/addToBasket', ['App\Controllers\CartController', 'addToBasket']);
            $r->post('/updateQuantity', ['App\Controllers\CartController', 'updateQuantity']);
            $r->post('/removeFromBasket', ['App\Controllers\CartController', 'removeFromBasket']);
            $r->post('/clearBasket', ['App\Controllers\CartController', 'clearBasket']);


            // Favourite routes
            $r->get('/favourites', ['App\Controllers\FavouriteController', 'viewFavourites']);
            $r->post('/favourites/toggle', ['App\Controllers\FavouriteController', 'toggleFavourite']);
            $r->post('/favourites/clear', ['App\Controllers\FavouriteController', 'clearFavourites']);

            // Appointment routes

            $r->get('/admin/dashboard', ['App\Controllers\AdminController', 'dashboard']);
            $r->get('/admin/users', ['App\Controllers\AdminController', 'manageUsers']);
            $r->get('/admin/products', ['App\Controllers\AdminController', 'manageProducts']);
            $r->get('/admin/addProductForm', ['App\Controllers\AdminController', 'addProductForm']);
            $r->post('/admin/addProduct', ['App\Controllers\AdminController', 'addProduct']);
            $r->post('/admin/users/delete', ['App\Controllers\AdminController', 'deleteUser']);
            $r->post('/admin/products/delete', ['App\Controllers\AdminController', 'deleteProduct']);
            $r->get('/admin/products/edit/{id}', ['App\Controllers\AdminController', 'editProductForm']);
            $r->post('/admin/products/update', ['App\Controllers\AdminController', 'updateProduct']);
            $r->get('/admin/orders', ['App\Controllers\AdminController', 'manageOrders']);
            $r->get('/admin/orders/{id:\d+}', ['App\Controllers\AdminController', 'orderShow']);
            $r->get('/admin/orders/{id:\d+}/items', ['App\Controllers\AdminController', 'orderItems']);
            $r->post('/admin/orders/{id:\d+}/status', ['App\Controllers\OrderController', 'adminUpdateStatus']);
            $r->get('/admin/appointments', ['App\Controllers\AppointmentController', 'adminIndex']);
            $r->post('/admin/appointments/slots/add', ['App\Controllers\AppointmentController', 'adminAddSlot']);
            $r->post('/admin/appointments/{id:\d+}/status', ['App\Controllers\AppointmentController', 'adminSetStatus']);


            // Orders + Checkout
            $r->get('/orders', ['App\Controllers\OrderController', 'index']);
            $r->get('/orders/{id:\d+}', ['App\Controllers\OrderController', 'show']);
            $r->post('/orders/{id:\d+}/cancel', ['App\Controllers\OrderController', 'cancel']);
            $r->get('/checkout', ['App\Controllers\CheckoutController', 'showCheckout']);
            $r->post('/checkout/place', ['App\Controllers\CheckoutController', 'processCheckout']);
            $r->get('/checkout/confirmation/{id:\d+}', ['App\Controllers\CheckoutController', 'confirmation']);


            // Customer appointment routes
            $r->get('/appointments', ['App\Controllers\AppointmentController', 'index']);
            $r->get('/appointments/book', ['App\Controllers\AppointmentController', 'bookForm']);
            $r->post('/appointments/book', ['App\Controllers\AppointmentController', 'book']);
            $r->get('/appointments/{id:\d+}/edit', ['App\Controllers\AppointmentController', 'editForm']);
            $r->post('/appointments/{id:\d+}/slot', ['App\Controllers\AppointmentController', 'updateSlot']);
            $r->post('/appointments/{id:\d+}/save', ['App\Controllers\AppointmentController', 'updateDetails']);
            $r->post('/appointments/{id:\d+}/cancel', ['App\Controllers\AppointmentController', 'cancel']);



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
                        $userRepository = new \App\Repositories\UserRepository();
                        $userService = new \App\Services\UserService($userRepository);
                        $controller = new $class($userService);
                        break;

                    case \App\Controllers\ProductController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $productService = new \App\Services\ProductService($productRepository);
                        $controller = new $class($productService);
                        break;

                    case \App\Controllers\CartController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $cartService = new \App\Services\CartService($productRepository);
                        $controller = new $class($cartService);
                        break;

                    case \App\Controllers\CheckoutController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $cartService = new \App\Services\CartService($productRepository);

                        $orderRepository = new \App\Repositories\OrderRepository();
                        $orderItemRepository = new \App\Repositories\OrderItemRepository();
                        $orderItemService = new \App\Services\OrderItemService($orderItemRepository);
                        $orderService = new \App\Services\OrderService($orderRepository, $orderItemService, $cartService);

                        $controller = new $class($cartService, $orderService, $orderItemService);
                        break;


                    case \App\Controllers\FavouriteController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $productService = new \App\Services\ProductService($productRepository);
                        $controller = new $class($productService);
                        break;

                    case \App\Controllers\OrderController::class:
                        $orderRepository = new \App\Repositories\OrderRepository();
                        $orderItemRepository = new \App\Repositories\OrderItemRepository();
                        $orderItemService = new \App\Services\OrderItemService($orderItemRepository);
                        $productRepository = new \App\Repositories\ProductRepository();
                        $cartService = new \App\Services\CartService($productRepository);
                        $orderService = new \App\Services\OrderService($orderRepository, $orderItemService, $cartService);
                        $controller = new $class($orderService, $orderItemService);
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

                        $productService = new \App\Services\ProductService($productRepository);
                        $userService = new \App\Services\UserService($userRepository);

                        // Orders dependencies (same pattern your project uses)
                        $orderRepository = new \App\Repositories\OrderRepository();
                        $orderItemRepository = new \App\Repositories\OrderItemRepository();
                        $orderItemService = new \App\Services\OrderItemService($orderItemRepository);
                        $cartService = new \App\Services\CartService($productRepository);
                        $orderService = new \App\Services\OrderService($orderRepository, $orderItemService, $cartService);

                        $appointmentRepository = new \App\Repositories\AppointmentRepository();
                        $appointmentSlotRepository = new \App\Repositories\AppointmentSlotRepository();
                        $appointmentService = new \App\Services\AppointmentService($appointmentRepository, $appointmentSlotRepository);
                        $controller = new $class(
                            $productService,
                            $userService,
                            $orderService,
                            $appointmentService
                        );
                        break;

                    case \App\Controllers\AuthController::class:
                        $userRepository = new \App\Repositories\UserRepository();
                        $userService = new \App\Services\UserService($userRepository);

                        $emailService = new \App\Services\EmailService();

                        $resetRepo = new \App\Repositories\PasswordResetTokenRepository();
                        $resetService = new \App\Services\PasswordResetService($resetRepo);

                        $controller = new $class($userService, $emailService, $resetService);
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
