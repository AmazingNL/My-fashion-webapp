<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Models\Product;
use App\Core\Middleware;
use App\Services\ProductService;
use App\Services\UserService;
use App\Services\ActivityLogService;

class AdminController extends ControllerBase
{
    private ProductService $productService;
    private UserService $userService;
    private ActivityLogService $logService;

    public function __construct(
        ProductService $productService,
        UserService $userService,
        ActivityLogService $logService
    ) {
        $this->productService = $productService;
        $this->userService = $userService;
        $this->logService = $logService;
    }

    /**
     * Admin Dashboard - Main page with statistics
     */
    public function dashboard(): void
    {
        Middleware::requireAdmin();

        // Get statistics
        $stats = [
            'totalProducts' => $this->productService->getTotalProductsCount(),
            'totalUsers' => $this->userService->getTotalUsersCount(),
            'totalOrders' => $this->getOrdersCount(),
            'pendingAppointments' => $this->getPendingAppointmentsCount(),
            'recentActivities' => $this->logService->getAllLogs(10),
        ];

        $this->logService->log(
            $this->currentUserId(),
            'Viewed Admin Dashboard',
            'admin',
            null,
            'Dashboard accessed'
        );

        $this->render('Admin/Dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats
        ]);
    }

    /**
     * Products Management Page
     */
    public function manageProducts(): void
    {
        Middleware::requireAdmin();

        $products = $this->productService->getAllProducts();

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
        ]);
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
        ]);
    }

    /**
     * Orders Management Page
     */
    public function manageOrders(): void
    {
        Middleware::requireAdmin();

        // This will be implemented with the Order repository
        $orders = []; // Placeholder

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
        ]);
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
        ]);
    }

    /**
     * View Activity Logs
     */
    public function viewLogs(): void
    {
        Middleware::requireAdmin();

        $page = (int) $this->input('page', 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $logs = $this->logService->getAllLogs($limit, $offset);

        $this->logService->log(
            $this->currentUserId(),
            'Viewed Activity Logs',
            'admin',
            null,
            "Page {$page} accessed"
        );

        $this->render('Admin/ActivityLogs', [
            'title' => 'Activity Logs',
            'logs' => $logs,
            'currentPage' => $page
        ]);
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

        if ($result) {
            $this->logService->log(
                $this->currentUserId(),
                'Deleted Product',
                'product',
                $productId,
                'Product permanently deleted'
            );

            $this->jsonResponse(['success' => true, 'message' => 'Product deleted']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete product'], 400);
        }
    }

    public function addProductForm(): void
    {
        // Render the add product form (implementation depends on your templating system)
        $this->render('Admin/AddProductForm', ['title' => 'Add Product',]);
    }
    public function addProduct(): void
    {
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



    // Helper methods
    private function getOrdersCount(): int
    {
        // Placeholder - will be implemented with OrderRepository
        return 0;
    }

    private function getPendingAppointmentsCount(): int
    {
        // Placeholder - will be implemented with AppointmentRepository
        return 0;
    }


}