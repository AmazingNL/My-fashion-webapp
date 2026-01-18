<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Services\IProductService;
use App\Models\Product;
use App\Services\IUserService;
use App\Services\IActivityLogService;
use App\Services\IOrderService;
use App\Services\IAppointmentService;

class AdminController extends ControllerBase 
{
    private IProductService $productService;
    private IUserService $userService;
    private IActivityLogService $logService;
    private IOrderService $orderService;
    private IAppointmentService $appointmentService;

    public function __construct(
        IProductService $productService,
        IUserService $userService,
        IActivityLogService $logService,
        IOrderService $orderService,
        IAppointmentService $appointmentService
    ) {
        $this->productService = $productService;
        $this->userService = $userService;
        $this->logService = $logService;
        $this->orderService = $orderService;
        $this->appointmentService = $appointmentService;
    }

    /**
     * Admin Dashboard - Main page with statistics
     */
    public function dashboard(): void
    {
        Middleware::requireAdmin();

        $products = $this->productService->getActiveProducts();
        $users = $this->userService->getAllUsers();

        $stats = [
            'totalProducts' => count($products),
            'totalUsers' => count($users),
            'totalOrders' => $this->getOrdersCount(),
            'pendingAppointments' => $this->getPendingAppointmentsCount(),
            'recentActivities' => $this->logService->getLogs(5),
        ];

        $this->render('Admin/Dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats
        ], 'admin');
    }


    /**
     * Products Management Page
     */
    public function manageProducts(): void
    {
        Middleware::requireAdmin();

        $products = $this->productService->getActiveProducts();

        $this->logService->log(
            $this->currentUserId(),
            'Viewed Products Management',
            'admin',
            null,
            'Products management page accessed'
        );

        $this->render('Admin/ManageProducts', [
            'title' => 'Manage Products',
            'products' => $products
        ], 'admin');
    }

    /**
     * Users Management Page
     */
    public function manageUsers(): void
    {
        Middleware::requireAdmin();

        $users = $this->userService->getAllUsers();

        $this->logService->log(
            $this->currentUserId(),
            'Viewed Users Management',
            'admin',
            null,
            'Users management page accessed'
        );

        $this->render('Admin/ManageUsers', [
            'title' => 'Manage Users',
            'users' => $users
        ], 'admin');
    }

    /**
     * Orders Management Page
     */
    public function manageOrders(): void
    {
        Middleware::requireAdmin();

        // get all orders 
        $orders = $this->orderService->getAllOrders();

        $this->logService->log(
            $this->currentUserId(),
            'Viewed Orders Management',
            'admin',
            null,
            'Orders management page accessed'
        );

        $this->render('Admin/ManageOrders', [
            'title' => 'Manage Orders',
            'orders' => $orders
        ], 'admin');
    }

    public function orderShow(string $id): void
    {
        Middleware::requireAdmin();

        $orderId = (int) $id;
        $order = $this->orderService->getOrderById($orderId);

        $this->render('Admin/OrderShow', [
            'title' => "Order #{$orderId}",
            'order' => $order,
        ], 'admin');
    }

    public function orderItems(string $id): void
    {
        Middleware::requireAdmin();

        $orderId = (int) $id;
        $order = $this->orderService->getOrderById($orderId);
        $items = $this->orderService->getItemsByOrderId($orderId);

        $this->render('Admin/OrderItems', [
            'title' => "Order #{$orderId} Items",
            'order' => $order,
            'items' => $items,
        ], 'admin');
    }



    /**
     * Appointments Management Page
     */
    public function manageAppointments(): void
    {
        Middleware::requireAdmin();

        // This will be implemented with the Appointment repository
        $appointments = []; // Placeholder

        $this->logService->log(
            $this->currentUserId(),
            'Viewed Appointments Management',
            'admin',
            null,
            'Appointments management page accessed'
        );

        $this->render('Admin/ManageAppointments', [
            'title' => 'Manage Appointments',
            'appointments' => $appointments
        ], 'admin');
    }

    /**
     * View Activity Logs
     */
    public function viewLogs(): void
    {
        Middleware::requireAdmin();

        $page = max(1, (int) $this->input('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $userId = $this->input('userId');
        $userId = ($userId !== null && $userId !== '') ? (int) $userId : null;

        $action = trim((string) $this->input('action', ''));
        $action = $action !== '' ? $action : null;

        $from = trim((string) $this->input('from', ''));
        $from = $from !== '' ? $from : null;

        $to = trim((string) $this->input('to', ''));
        $to = $to !== '' ? $to : null;

        $logs = $this->logService->getLogs($limit, $offset, $userId, $action, $from, $to);
        $total = $this->logService->countLogs($userId, $action, $from, $to);
        $pages = (int) ceil(max(1, $total) / $limit);

        $this->logService->log(
            $this->currentUserId(),
            'Viewed Activity Logs',
            'admin',
            null,
            "Filters: userId=" . ($userId ?? 'any') . ", action=" . ($action ?? 'any') .
            ", from=" . ($from ?? 'any') . ", to=" . ($to ?? 'any') . ", page={$page}"
        );

        $this->render('Admin/ActivityLogs', [
            'title' => 'Activity Logs',
            'logs' => $logs,
            'page' => $page,
            'pages' => max(1, $pages),
            'total' => $total,
            'filters' => [
                'userId' => $userId,
                'action' => $action,
                'from' => $from,
                'to' => $to,
            ],
        ], 'admin');
    }


    /**
     * Export Activity Logs
     */
    public function exportLogs(): void
    {
        Middleware::requireAdmin();

        $filepath = $this->logService->exportLogsToFile();

        $this->logService->log(
            $this->currentUserId(),
            'Exported Activity Logs',
            'admin',
            null,
            'Activity logs exported to file'
        );

        // Provide download
        if (file_exists($filepath)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        } else {
            $this->jsonResponse(['error' => 'File not found'], 404);
        }
    }

    /**
     * Update User Status (activate/deactivate)
     */
    public function updateUserStatus(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $userId = (int) $this->input('userId', 0);
        $isActive = (bool) $this->input('isActive', false);

        $result = $this->userService->updateUserStatus($userId, $isActive);

        if ($result) {
            $this->logService->log(
                $this->currentUserId(),
                'Updated User Status',
                'user',
                $userId,
                "User status changed to: " . ($isActive ? 'active' : 'inactive')
            );

            $this->jsonResponse(['success' => true, 'message' => 'User status updated']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update user'], 400);
        }
    }

    /**
     * Delete Product
     */
    public function deleteProduct(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $productId = (int) $this->input('productId', 0);

        $result = $this->productService->deleteProduct($productId);

        if (isset($result['success'])) {
            $this->logService->log(
                $this->currentUserId(),
                'Deleted Product',
                'product',
                $productId,
                'Product deleted'
            );

            $this->jsonResponse(['success' => true, 'message' => $result['success']]);
            return;
        }

        $this->jsonResponse([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to delete product'
        ], 400);
    }


    public function addProductForm(): void
    {
        // Render the add product form (implementation depends on your templating system)
        $this->render(
            'Admin/AddProductForm',
            ['title' => 'Add Product',],
            'admin'
        );
    }
    public function addProduct(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        // Extract path and error from the array
        [$imagePath, $imageError] = $this->saveUploadedProductImage($_FILES['image'] ?? []);

        // Check for upload errors
        if ($imageError !== null) {
            $this->jsonResponse(['errors' => [$imageError]], 400);
            return;
        }

        $product = new Product(
            null,
            trim($this->input('name', '')),
            trim($this->input('description', '')),
            (float) $this->input('price', 0.0),
            trim($this->input('category', '')),
            (int) $this->input('stock', 0),
            $imagePath, // <- Now just the string path, not the array
            null,
            null,
            true
        );

        $variants = $this->input('variants', []);
        try {
            $result = $this->productService->createProductWithVariants($product, $variants);

            if (!empty($result['errors'])) {
                $this->jsonResponse(['errors' => $result['errors']], 400);
                return;
            }

            $this->jsonResponse(['message' => 'Product + variants added successfully'], 201);
            return;
        } catch (\Throwable $e) {
            $this->jsonResponse(['errors' => ['An unexpected error occurred.']], 500);
            return;
        }
    }

    private function saveUploadedProductImage(array $file): array
    {

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
            return [null, null]; // image optional

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return [null, 'File exceeds upload_max_filesize'];
        }

        $maxBytes = 5 * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxBytes)
            return [null, 'Image must be 5MB or smaller.'];

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp))
            return [null, 'Invalid uploaded file.'];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime]))
            return [null, 'Only JPG, PNG, or WEBP images are allowed.'];

        $dirFs = __DIR__ . '/../../Public/images/products';
        if (!is_dir($dirFs) && !mkdir($dirFs, 0755, true))
            return [null, 'Could not create image folder.'];

        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $destFs = $dirFs . '/' . $name;

        if (!move_uploaded_file($tmp, $destFs))
            return [null, 'Could not save uploaded image.'];

        return ['/images/products/' . $name, null];
    }

    /**
     * GET /admin/products/edit/{id}
     */
    public function editProductForm(string $id): void
    {
        Middleware::requireAdmin();

        $productId = (int) $id;

        $product = $this->productService->getProductById($productId);
        if (!$product) {
            $this->redirect('/admin/products?error=product_not_found');
            return;
        }

        $variants = $this->productService->getVariantsByProductId($productId);

        $this->logService->log(
            $this->currentUserId(),
            'Opened Product Edit Form',
            'product',
            $productId,
            'Edit form opened'
        );

        $this->render('Admin/EditProduct', [
            'title' => 'Edit Product',
            'product' => $product,
            'variants' => $variants
        ], 'admin');
    }

    public function updateProduct(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $productId = $this->requireProductId();
        $existing = $this->requireExistingProduct($productId);

        $finalImagePath = $this->resolveProductImagePath($existing);

        [$name, $category, $description, $price, $stock] = $this->readAndValidateProductFields();

        $product = $this->buildProductEntity(
            $productId,
            $name,
            $description,
            $price,
            $category,
            $stock,
            $finalImagePath
        );

        $this->persistProductOrFail($product);

        $variantPayloads = $this->readVariantPayloads();
        $this->applyVariantChanges($productId, $variantPayloads);

        $this->logProductUpdate($productId);

        $this->redirect('/admin/products/edit/' . $productId . '?saved=1');
    }


    // Helper methods

    private function requireProductId(): int
    {
        $productId = (int) $this->input('productId', 0);
        if ($productId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid productId'], 400);
        }
        return $productId;
    }

    private function requireExistingProduct(int $productId)
    {
        $existing = $this->productService->getProductById($productId);
        if (!$existing) {
            $this->jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
        }
        return $existing;
    }

    private function resolveProductImagePath($existing): ?string
    {
        // Optional upload
        [$newImagePath, $imageError] = $this->saveUploadedProductImage($_FILES['image'] ?? []);
        if ($imageError !== null) {
            $this->jsonResponse(['success' => false, 'message' => $imageError], 400);
        }

        if ($newImagePath)
            return $newImagePath;

        // Keep old image if no upload
        if (is_array($existing))
            return $existing['image'] ?? null;
        if (is_object($existing) && method_exists($existing, 'getImage'))
            return $existing->getImage();

        return null;
    }

    private function readAndValidateProductFields(): array
    {
        $name = trim((string) $this->input('productName', ''));
        $category = trim((string) $this->input('category', ''));
        $description = trim((string) $this->input('description', ''));
        $price = (float) $this->input('price', 0);
        $stock = (int) $this->input('stock', 0);

        if ($name === '' || $category === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Product name and category are required'], 400);
        }
        if ($price < 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Price cannot be negative'], 400);
        }
        if ($stock < 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Stock cannot be negative'], 400);
        }

        return [$name, $category, $description, $price, $stock];
    }

    private function buildProductEntity(
        int $productId,
        string $name,
        string $description,
        float $price,
        string $category,
        int $stock,
        ?string $imagePath
    ): Product {
        return new Product(
            $productId,
            $name,
            $description,
            $price,
            $category,
            $stock,
            $imagePath,
            null,
            null,
            true
        );
    }

    private function persistProductOrFail(Product $product): void
    {
        $ok = $this->productService->updateProduct($product);
        if (!$ok) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update product'], 400);
        }
    }

    /**
     * Reads parallel arrays from POST into a safe structure.
     */
    private function readVariantPayloads(): array
    {
        $ids = $this->asArray($this->input('variantId', []));
        $sizes = $this->asArray($this->input('variantSize', []));
        $colours = $this->asArray($this->input('variantColour', []));
        $stocks = $this->asArray($this->input('variantStock', []));
        $prices = $this->asArray($this->input('variantPrice', []));
        $deletes = $this->asArray($this->input('variantDelete', []));

        $count = max(count($ids), count($sizes), count($colours), count($stocks), count($prices), count($deletes));

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'variantId' => (int) ($ids[$i] ?? 0),
                'delete' => (int) ($deletes[$i] ?? 0),
                'size' => trim((string) ($sizes[$i] ?? '')),
                'colour' => trim((string) ($colours[$i] ?? '')),
                'stock' => (int) ($stocks[$i] ?? 0),
                'price' => (float) ($prices[$i] ?? 0),
            ];
        }
        return $rows;
    }

    private function applyVariantChanges(int $productId, array $variantRows): void
    {
        foreach ($variantRows as $row) {
            if ($this->shouldDeleteVariant($row)) {
                $this->productService->deleteVariant((int) $row['variantId']);
                continue;
            }

            if ($this->isVariantRowSkippable($row)) {
                continue;
            }

            if ($this->isVariantRowInvalid($row)) {
                // If you want: hard fail instead of skip
                continue;
            }

            if ((int) $row['variantId'] > 0) {
                $this->productService->updateVariantByFields(
                    (int) $row['variantId'],
                    $row['size'],
                    $row['colour'],
                    (int) $row['stock'],
                    (float) $row['price']
                );
            } else {
                $this->productService->createVariantByFields(
                    $productId,
                    $row['size'],
                    $row['colour'],
                    (int) $row['stock'],
                    (float) $row['price']
                );
            }
        }
    }

    private function shouldDeleteVariant(array $row): bool
    {
        return ((int) $row['variantId'] > 0) && ((int) $row['delete'] === 1);
    }

    private function isVariantRowSkippable(array $row): bool
    {
        // empty new row (e.g. user clicked add then didn't fill it)
        return $row['size'] === '' && $row['colour'] === '' && (int) $row['variantId'] === 0;
    }

    private function isVariantRowInvalid(array $row): bool
    {
        // incomplete variant row
        if ($row['size'] === '' || $row['colour'] === '')
            return true;
        if ((int) $row['stock'] < 0)
            return true;
        if ((float) $row['price'] < 0)
            return true;
        return false;
    }

    private function logProductUpdate(int $productId): void
    {
        $this->logService->log(
            $this->currentUserId(),
            'Updated Product + Variants',
            'product',
            $productId,
            'Product updated (including variants)'
        );
    }

    private function asArray($value): array
    {
        return is_array($value) ? $value : [];
    }

    private function getOrdersCount(): int
    {
        return $this->orderService->countAllOrders();
    }

    private function getPendingAppointmentsCount(): int
    {
        return $this->appointmentService->countPending();
    }



}