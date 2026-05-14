<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Mappers\AdminDashboardStatsMapper;
use App\Mappers\OrderMapper;
use App\Mappers\ProductResponseMapper;
use App\Mappers\ProductRequestMapper;
use App\Mappers\ResponseUserMapper;
use App\Services\EmailLogService;
use App\Services\IAppointmentService;
use App\Services\IOrderService;
use App\Services\IProductService;
use App\Services\IUserService;
use RuntimeException;

class AdminController extends ControllerBase
{
    private IProductService $productService;
    private IUserService $userService;
    private IOrderService $orderService;
    private IAppointmentService $appointmentService;
    private EmailLogService $emailLogService;

    public function __construct(
        IProductService $productService,
        IUserService $userService,
        IOrderService $orderService,
        IAppointmentService $appointmentService,
        ?EmailLogService $emailLogService = null
    ) {
        $this->productService = $productService;
        $this->userService = $userService;
        $this->orderService = $orderService;
        $this->appointmentService = $appointmentService;
        $this->emailLogService = $emailLogService ?? new EmailLogService();
    }

    public function dashboard(): void
    {
        try {
            Middleware::requireAdmin();

            $stats = [
                'totalProducts' => count($this->productService->getActiveProducts()),
                'totalUsers' => count($this->userService->getAllUsers()),
                'totalOrders' => $this->orderService->countAllOrders(),
                'pendingAppointments' => $this->appointmentService->countPending(),
                'recentActivities' => [],
            ];

            $this->jsonResponse($this->success([
                'stats' => AdminDashboardStatsMapper::mapToAdminDashboardStatsDto($stats),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load dashboard.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function manageProducts(): void
    {
        try {
            Middleware::requireAdmin();

            $this->jsonResponse($this->success([
                'products' => ProductResponseMapper::mapToProductDtos($this->productService->getActiveProducts()),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load products.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function manageUsers(): void
    {
        try {
            Middleware::requireAdmin();

            $this->jsonResponse($this->success([
                'users' => ResponseUserMapper::responseUserMappers($this->userService->getAllUsers()),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load users.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function manageOrders(): void
    {
        try {
            Middleware::requireAdmin();

            $this->jsonResponse($this->success([
                'orders' => OrderMapper::mapToOrderDtos($this->orderService->getAllOrders()),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load orders.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function emailLogs(): void
    {
        try {
            Middleware::requireAdmin();

            $this->jsonResponse($this->success([
                'emails' => $this->emailLogService->listEmails(),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load emails.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function emailLog(string $fileName): void
    {
        try {
            Middleware::requireAdmin();

            $this->jsonResponse($this->success([
                'email' => $this->emailLogService->getEmail($fileName),
            ]));
        } catch (\RuntimeException $e) {
            $this->jsonResponse($this->error($e->getMessage()), 404);
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load email.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function orderShow(string $id): void
    {
        try {
            Middleware::requireAdmin();

            $orderId = (int) $id;
            $order = $this->orderService->getOrderById($orderId);

            $this->jsonResponse($this->success([
                'order' => $order ? OrderMapper::mapToOrderDto($order) : null,
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load order.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function orderItems(string $id): void
    {
        try {
            Middleware::requireAdmin();

            $orderId = (int) $id;
            $order = $this->orderService->getOrderById($orderId);
            $items = $this->orderService->getItemsByOrderId($orderId);

            $this->jsonResponse($this->success(OrderMapper::mapToOrderDetailsDto($order, $items)));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load order items.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function deleteUser(string $id): void
    {
        try {
            Middleware::requireAdmin();

            $userId = (int) $id;

            if ($userId <= 0) {
                $this->jsonResponse($this->error('Invalid user id.'), 422);
            }

            $user = $this->userService->getUserById($userId);
            if (!$user) {
                $this->jsonResponse($this->error('User not found.'), 404);
            }

            if (strtolower((string) ($user->role ?? '')) === 'admin') {
                $this->jsonResponse($this->error('Admin users cannot be deleted.'), 422);
            }

            $ok = $this->userService->deleteUser($userId);
            $this->jsonResponse($ok
                ? $this->success(['userId' => $userId], 'User deleted successfully.')
                : $this->error('Failed to delete user.'),
                $ok ? 200 : 500
            );
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to delete user.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function deleteProduct(string $id): void
    {
        try {
            Middleware::requireAdmin();

            $productId = (int) $id;
            if ($productId <= 0) {
                $this->jsonResponse($this->error('Invalid product id.'), 422);
            }

            $result = $this->productService->deleteProduct($productId);
            if (!empty($result['success'])) {
                $this->jsonResponse($this->success(['productId' => $productId], (string) $result['success']));
            }

            $this->jsonResponse($this->error((string) ($result['error'] ?? 'Failed to delete product.')), 422);
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to delete product.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function addProduct(): void
    {
        try {
            Middleware::requireAdmin();

            [$imagePath, $imageError] = $this->productService->saveUploadedProductImage($_FILES['image'] ?? []);
            if ($imageError !== null) {
                $this->jsonResponse($this->error($imageError), 422);
            }

            if ($imagePath === null) {
                $this->jsonResponse($this->error('Product image is required.'), 422);
            }

            $dto = ProductRequestMapper::mapToProductRequestDto($this->requestData(), $imagePath);
            $product = ProductRequestMapper::mapToProduct($dto);
            $variants = ProductRequestMapper::mapToVariantInputRows($dto->variants);

            $result = $this->productService->createProductWithVariants($product, $variants);
            if (!empty($result['errors'])) {
                $this->jsonResponse($this->error('Product validation failed.', (array) $result['errors']), 422);
            }

            $productId = (int) ($result['productId'] ?? 0);
            $savedProduct = $productId > 0 ? $this->productService->getProductById($productId) : null;

            $this->jsonResponse($this->success(ProductResponseMapper::mapToProductDetailsDto(
                $savedProduct ?? $product,
                $productId > 0 ? $this->productService->getVariantsByProductId($productId) : []
            ), 'Product and variants added successfully.'), 201);
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('An unexpected error occurred.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function editProductForm(string $id): void
    {
        try {
            Middleware::requireAdmin();

            $productId = (int) $id;
            $product = $this->productService->getProductById($productId);

            if (!$product) {
                $this->jsonResponse($this->error('Product not found.'), 404);
            }

            $this->jsonResponse($this->success(ProductResponseMapper::mapToProductDetailsDto(
                $product,
                $this->productService->getVariantsByProductId($productId)
            )));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load product.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function updateProduct(string $id): void
    {
        try {
            Middleware::requireAdmin();

            $productId = (int) $id;

            if ($productId <= 0) {
                throw new RuntimeException('Invalid product id.');
            }

            $existing = $this->requireExistingProduct($productId);
            $finalImagePath = $this->resolveProductImagePath($existing);
            $dto = $this->readAndValidateProductFields($finalImagePath);
            $product = ProductRequestMapper::mapToProduct($dto, $productId);

            $updateResult = $this->productService->updateProduct($product);
            if (!empty($updateResult['error'])) {
                throw new RuntimeException((string) $updateResult['error']);
            }

            $variantRows = ProductRequestMapper::mapToVariantChangeDtos($this->requestData());
            $this->productService->applyVariantChanges($productId, $variantRows);
            $savedProduct = $this->productService->getProductById($productId) ?? $product;

            $this->jsonResponse($this->success(ProductResponseMapper::mapToProductDetailsDto(
                $savedProduct,
                $this->productService->getVariantsByProductId($productId)
            ), 'Product updated successfully.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    private function requireExistingProduct(int $productId)
    {
        $existing = $this->productService->getProductById($productId);
        if (!$existing) {
            throw new RuntimeException('Product not found.');
        }

        return $existing;
    }

    private function resolveProductImagePath($existing): ?string
    {
        return $this->productService->resolveProductImagePath($existing, $_FILES['image'] ?? []);
    }

    private function readAndValidateProductFields(?string $imagePath)
    {
        $dto = ProductRequestMapper::mapToProductRequestDto($this->requestData(), $imagePath);
        $errors = $this->productService->validateProductRequest($dto);

        if (!empty($errors)) {
            throw new RuntimeException(implode(' ', $errors));
        }

        return $dto;
    }

}
