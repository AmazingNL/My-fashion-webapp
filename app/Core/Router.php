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
            // Auth routes
            $r->post('/auth/login', ['App\Controllers\AuthController', 'login']);
            $r->post('/auth/logout', ['App\Controllers\AuthController', 'logout']);
            $r->post('/auth/password-reset', ['App\Controllers\AuthController', 'requestReset']);
            $r->post('/auth/password-reset/verify', ['App\Controllers\AuthController', 'verifyResetCode']);

            // User routes
            $r->get('/users', ['App\Controllers\UserController', 'viewUsers']);
            $r->post('/users', ['App\Controllers\UserController', 'registerUser']);
            $r->get('/about', ['App\Controllers\UserController', 'aboutUs']);

            // Product routes
            $r->get('/products', ['App\Controllers\ProductController', 'productLists']);
            $r->get('/products/{id}', ['App\Controllers\ProductController', 'productDetails']);

            // Cart routes
            $r->get('/cart', ['App\Controllers\CartController', 'viewCart']);
            $r->post('/cart/items', ['App\Controllers\CartController', 'addToCart']);
            $r->addRoute('PATCH', '/cart/items', ['App\Controllers\CartController', 'updateQuantity']);
            $r->addRoute('DELETE', '/cart/items', ['App\Controllers\CartController', 'removeFromCart']);
            $r->addRoute('DELETE', '/cart', ['App\Controllers\CartController', 'clearCart']);

            // Favourite routes
            $r->get('/favourites', ['App\Controllers\FavouriteController', 'viewFavourites']);
            $r->post('/favourites/items', ['App\Controllers\FavouriteController', 'toggleFavourite']);
            $r->addRoute('DELETE', '/favourites', ['App\Controllers\FavouriteController', 'clearFavourites']);

            // Admin routes
            $r->get('/admin/dashboard', ['App\Controllers\AdminController', 'dashboard']);
            $r->get('/admin/users', ['App\Controllers\AdminController', 'manageUsers']);
            $r->addRoute('DELETE', '/admin/users/{id:\d+}', ['App\Controllers\AdminController', 'deleteUser']);
            $r->get('/admin/products', ['App\Controllers\AdminController', 'manageProducts']);
            $r->post('/admin/products', ['App\Controllers\AdminController', 'addProduct']);
            $r->get('/admin/products/{id:\d+}', ['App\Controllers\AdminController', 'editProductForm']);
            $r->addRoute('PATCH', '/admin/products/{id:\d+}', ['App\Controllers\AdminController', 'updateProduct']);
            $r->addRoute('DELETE', '/admin/products/{id:\d+}', ['App\Controllers\AdminController', 'deleteProduct']);
            $r->get('/admin/orders', ['App\Controllers\AdminController', 'manageOrders']);
            $r->get('/admin/orders/{id:\d+}', ['App\Controllers\AdminController', 'orderShow']);
            $r->get('/admin/orders/{id:\d+}/items', ['App\Controllers\AdminController', 'orderItems']);
            $r->addRoute('PATCH', '/admin/orders/{id:\d+}/status', ['App\Controllers\OrderController', 'adminUpdateStatus']);
            $r->get('/admin/appointments', ['App\Controllers\AppointmentController', 'adminIndex']);
            $r->post('/admin/appointments/slots', ['App\Controllers\AppointmentController', 'adminAddSlot']);
            $r->addRoute('PATCH', '/admin/appointments/{id:\d+}/status', ['App\Controllers\AppointmentController', 'adminSetStatus']);

            // Orders + Checkout
            $r->get('/orders', ['App\Controllers\OrderController', 'index']);
            $r->get('/orders/{id:\d+}', ['App\Controllers\OrderController', 'show']);
            $r->post('/orders/{id:\d+}/cancel', ['App\Controllers\OrderController', 'cancel']);
            $r->get('/checkout', ['App\Controllers\CheckoutController', 'showCheckout']);
            $r->post('/checkout', ['App\Controllers\CheckoutController', 'processCheckout']);
            $r->get('/checkout/confirmation/{id:\d+}', ['App\Controllers\CheckoutController', 'confirmation']);

            // Customer appointment routes
            $r->get('/appointments', ['App\Controllers\AppointmentController', 'index']);
            $r->get('/appointments/slots', ['App\Controllers\AppointmentController', 'bookForm']);
            $r->post('/appointments', ['App\Controllers\AppointmentController', 'book']);
            $r->get('/appointments/{id:\d+}', ['App\Controllers\AppointmentController', 'editForm']);
            $r->addRoute('PATCH', '/appointments/{id:\d+}/slot', ['App\Controllers\AppointmentController', 'updateSlot']);
            $r->addRoute('PATCH', '/appointments/{id:\d+}', ['App\Controllers\AppointmentController', 'updateDetails']);
            $r->addRoute('DELETE', '/appointments/{id:\d+}', ['App\Controllers\AppointmentController', 'cancel']);

        });

        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (false !== $pos = strpos($uri, '?'))
            $uri = substr($uri, 0, $pos);
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->json($this->error('Page not found.'), 404);
                return;

            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->json($this->error('Method not allowed.'), 405);
                return;

            case Dispatcher::FOUND:
                [$class, $method] = $routeInfo[1];
                $vars = $routeInfo[2];

                if (!class_exists($class)) {
                    $this->json($this->error('Controller not found.', ['controller' => $class]), 500);
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
                        $cartRepository = new \App\Repositories\CartRepository();
                        $cartService = new \App\Services\CartService($productRepository, $cartRepository);
                        $controller = new $class($cartService);
                        break;

                    case \App\Controllers\CheckoutController::class:
                        $productRepository = new \App\Repositories\ProductRepository();
                        $cartRepository = new \App\Repositories\CartRepository();
                        $cartService = new \App\Services\CartService($productRepository, $cartRepository);

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
                        $cartRepository = new \App\Repositories\CartRepository();
                        $cartService = new \App\Services\CartService($productRepository, $cartRepository);
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
                        $cartRepository = new \App\Repositories\CartRepository();
                        $cartService = new \App\Services\CartService($productRepository, $cartRepository);
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
                    $this->json($this->error('Method not found.', ['method' => $class . '::' . $method]), 500);
                    return;
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

    private function json($response, int $statusCode): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_THROW_ON_ERROR);
    }

    private function error(string $message, array $errors = [], $data = null): array
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $response;
    }
}
