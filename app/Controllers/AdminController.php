<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Models\Product;
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

    public function __construct(
        IProductService $productService,
        IUserService $userService,
        IOrderService $orderService,
        IAppointmentService $appointmentService
    ) {
        $this->productService = $productService;
        $this->userService = $userService;
        $this->orderService = $orderService;
        $this->appointmentService = $appointmentService;
    }

    public function dashboard(): void
    {
        Middleware::requireAdmin();

        $stats = [
            'totalProducts' => count($this->productService->getActiveProducts()),
            'totalUsers' => count($this->userService->getAllUsers()),
            'totalOrders' => $this->orderService->countAllOrders(),
            'pendingAppointments' => $this->appointmentService->countPending(),
            'recentActivities' => [],
        ];

        $this->render('Admin/Dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
        ], 'admin');
    }

    public function manageProducts(): void
    {
        Middleware::requireAdmin();
        [$success, $error] = $this->consumeFlash('admin');

        $this->render('Admin/ManageProducts', [
            'title' => 'Manage Products',
            'products' => $this->productService->getActiveProducts(),
            'success' => $success,
            'error' => $error,
        ], 'admin');
    }

    public function manageUsers(): void
    {
        Middleware::requireAdmin();
        [$success, $error] = $this->consumeFlash('admin');

        $this->render('Admin/ManageUsers', [
            'title' => 'Manage Users',
            'users' => $this->userService->getAllUsers(),
            'success' => $success,
            'error' => $error,
        ], 'admin');
    }

    public function manageOrders(): void
    {
        Middleware::requireAdmin();
        [$success, $error] = $this->consumeFlash('admin');

        $this->render('Admin/ManageOrders', [
            'title' => 'Manage Orders',
            'orders' => $this->orderService->getAllOrders(),
            'success' => $success,
            'error' => $error,
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

    public function deleteUser(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $userId = (int) $this->input('userId', 0);

        if ($userId <= 0) {
            $this->setAdminFlash('Invalid user id.', 'error');
            $this->redirect('/admin/users');
            return;
        }

        $user = $this->userService->getUserById($userId);
        if (!$user) {
            $this->setAdminFlash('User not found.', 'error');
            $this->redirect('/admin/users');
            return;
        }

        if (strtolower((string) ($user->role ?? '')) === 'admin') {
            $this->setAdminFlash('Admin users cannot be deleted.', 'error');
            $this->redirect('/admin/users');
            return;
        }

        $ok = $this->userService->deleteUser($userId);
        if ($ok) {
            $this->setAdminFlash('User deleted successfully.', 'success');
        } else {
            $this->setAdminFlash('Failed to delete user.', 'error');
        }

        $this->redirect('/admin/users');
    }

    public function deleteProduct(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $productId = (int) $this->input('productId', 0);
        if ($productId <= 0) {
            $this->setFlash('admin', 'Invalid product id.', 'error');
            $this->redirect('/admin/products');
            return;
        }

        $result = $this->productService->deleteProduct($productId);
        if (!empty($result['success'])) {
            $this->setAdminFlash((string) $result['success'], 'success');
        } else {
            $this->setAdminFlash((string) ($result['error'] ?? 'Failed to delete product.'), 'error');
        }

        $this->redirect('/admin/products');
    }

    public function addProductForm(): void
    {
        Middleware::requireAdmin();
        [$success, $error] = $this->consumeAdminFlash();

        $this->render('Admin/AddProductForm', [
            'title' => 'Add Product',
            'success' => $success,
            'error' => $error,
        ], 'admin');
    }

    public function addProduct(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        [$imagePath, $imageError] = $this->saveUploadedProductImage($_FILES['image'] ?? []);
        if ($imageError !== null) {
            $this->setAdminFlash($imageError, 'error');
            $this->redirect('/admin/addProductForm');
            return;
        }

        $product = new Product(
            null,
            trim((string) $this->input('name', '')),
            trim((string) $this->input('description', '')),
            (float) $this->input('price', 0.0),
            trim((string) $this->input('category', '')),
            (int) $this->input('stock', 0),
            $imagePath,
            null,
            null,
            true
        );

        $variants = $this->input('variants', []);

        try {
            $result = $this->productService->createProductWithVariants($product, $variants);
            if (!empty($result['errors'])) {
                $this->setFlash('admin', (string) implode(' | ', (array) $result['errors']), 'error');
                $this->redirect('/admin/addProductForm');
                return;
            }

            $this->setFlash('admin', 'Product and variants added successfully.', 'success');
            $this->redirect('/admin/products');
        } catch (\Throwable $e) {
            $this->setFlash('admin', 'An unexpected error occurred.', 'error');
            $this->redirect('/admin/addProductForm');
        }
    }

    public function editProductForm(string $id): void
    {
        Middleware::requireAdmin();

        $productId = (int) $id;
        $product = $this->productService->getProductById($productId);

        if (!$product) {
            $this->setFlash('admin', 'Product not found.', 'error');
            $this->redirect('/admin/products');
            return;
        }

        [$success, $error] = $this->consumeFlash('admin');

        $this->render('Admin/EditProduct', [
            'title' => 'Edit Product',
            'product' => $product,
            'variants' => $this->productService->getVariantsByProductId($productId),
            'success' => $success,
            'error' => $error,
        ], 'admin');
    }

    public function updateProduct(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $productId = (int) $this->input('productId', 0);

        try {
            if ($productId <= 0) {
                throw new RuntimeException('Invalid product id.');
            }

            $existing = $this->requireExistingProduct($productId);
            $finalImagePath = $this->resolveProductImagePath($existing);
            [$name, $category, $description, $price, $stock] = $this->readAndValidateProductFields();

            $product = new Product(
                $productId,
                $name,
                $description,
                $price,
                $category,
                $stock,
                $finalImagePath,
                null,
                null,
                true
            );

            $updateResult = $this->productService->updateProduct($product);
            if (!empty($updateResult['error'])) {
                throw new RuntimeException((string) $updateResult['error']);
            }

            $variantRows = $this->readVariantPayloads();
            $this->applyVariantChanges($productId, $variantRows);

            $this->setFlash('admin', 'Product updated successfully.', 'success');
            $this->redirect('/admin/products/edit/' . $productId);
        } catch (\Throwable $e) {
            $this->setFlash('admin', $e->getMessage(), 'error');
            $this->redirect('/admin/products/edit/' . max(1, $productId));
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

    private function saveUploadedProductImage(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return [null, 'File exceeds upload limit.'];
        }

        $maxBytes = 5 * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxBytes) {
            return [null, 'Image must be 5MB or smaller.'];
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return [null, 'Invalid uploaded file.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            return [null, 'Only JPG, PNG, or WEBP images are allowed.'];
        }

        $dirFs = __DIR__ . '/../../Public/images/products';
        if (!is_dir($dirFs) && !mkdir($dirFs, 0755, true)) {
            return [null, 'Could not create image folder.'];
        }

        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $destFs = $dirFs . '/' . $name;

        if (!move_uploaded_file($tmp, $destFs)) {
            return [null, 'Could not save uploaded image.'];
        }

        return ['/images/products/' . $name, null];
    }

    private function resolveProductImagePath($existing): ?string
    {
        [$newImagePath, $imageError] = $this->saveUploadedProductImage($_FILES['image'] ?? []);
        if ($imageError !== null) {
            throw new RuntimeException($imageError);
        }

        if ($newImagePath !== null) {
            return $newImagePath;
        }

        if (is_array($existing)) {
            return (string) ($existing['image'] ?? '');
        }

        if (is_object($existing) && method_exists($existing, 'getImage')) {
            return (string) $existing->getImage();
        }

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
            throw new RuntimeException('Product name and category are required.');
        }
        if ($price < 0) {
            throw new RuntimeException('Price cannot be negative.');
        }
        if ($stock < 0) {
            throw new RuntimeException('Stock cannot be negative.');
        }

        return [$name, $category, $description, $price, $stock];
    }

    private function readVariantPayloads(): array
    {
        $ids = $this->asArray($this->input('variantId', []));
        $sizes = $this->asArray($this->input('variantSize', []));
        $colours = $this->asArray($this->input('variantColour', []));
        $stocks = $this->asArray($this->input('variantStock', []));
        $prices = $this->asArray($this->input('variantPrice', []));
        $deleteIds = array_map('intval', $this->asArray($this->input('variantDeleteIds', [])));

        $count = max(count($ids), count($sizes), count($colours), count($stocks), count($prices));

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $variantId = (int) ($ids[$i] ?? 0);
            $rows[] = [
                'variantId' => $variantId,
                'delete' => in_array($variantId, $deleteIds, true) ? 1 : 0,
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

            if ($this->isVariantRowSkippable($row) || $this->isVariantRowInvalid($row)) {
                continue;
            }

            if ((int) $row['variantId'] > 0) {
                $this->productService->updateVariantByFields(
                    (int) $row['variantId'],
                    (string) $row['size'],
                    (string) $row['colour'],
                    (int) $row['stock'],
                    (float) $row['price']
                );
            } else {
                $this->productService->createVariantByFields(
                    $productId,
                    (string) $row['size'],
                    (string) $row['colour'],
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
        return $row['size'] === '' && $row['colour'] === '' && (int) $row['variantId'] === 0;
    }

    private function isVariantRowInvalid(array $row): bool
    {
        if ($row['size'] === '' || $row['colour'] === '') {
            return true;
        }

        if ((int) $row['stock'] < 0) {
            return true;
        }

        return (float) $row['price'] < 0;
    }

    private function asArray($value): array
    {
        return is_array($value) ? $value : [];
    }

    private function setAdminFlash(string $message, string $type = 'success'): void
    {
        $this->setFlash('admin', $message, $type);
    }

    private function consumeAdminFlash(): array
    {
        return $this->consumeFlash('admin');
    }
}
