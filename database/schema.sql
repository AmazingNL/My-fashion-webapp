-- Custom Female Clothing Web Application Database Schema
-- Student: Amazinggrace Iruoma (726138)

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS appointment_slots;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- Users Table
CREATE TABLE users (
    userId INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    isActive BOOLEAN DEFAULT TRUE,
    emailVerified BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE products (
    productId INT AUTO_INCREMENT PRIMARY KEY,
    productName VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100),
    stock INT DEFAULT 0,
    image VARCHAR(500),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    isActive BOOLEAN DEFAULT TRUE,
    INDEX idx_category (category),
    INDEX idx_active (isActive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Variants Table (sizes, colors)
CREATE TABLE product_variants (
    variantId INT AUTO_INCREMENT PRIMARY KEY,
    productId INT NOT NULL,
    size VARCHAR(10),
    colour VARCHAR(50),
    stockQuantity INT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (productId) REFERENCES products(productId) ON DELETE CASCADE,
    INDEX idx_product (productId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shopping Cart Table (session-based backup)
CREATE TABLE cart_items (
    cartItemId INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NULL,
    sessionId VARCHAR(255) NULL,
    productId INT NOT NULL,
    variantId INT NULL,
    quantity INT DEFAULT 1,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
    FOREIGN KEY (productId) REFERENCES products(productId) ON DELETE CASCADE,
    FOREIGN KEY (variantId) REFERENCES product_variants(variantId) ON DELETE SET NULL,
    INDEX idx_user (userId),
    INDEX idx_session (sessionId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Table
CREATE TABLE orders (
    orderId INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    totalAmount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shippingAddress TEXT,
    billingAddress TEXT,
    paymentMethod VARCHAR(50),
    paymentStatus ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE RESTRICT,
    INDEX idx_user (userId),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items Table
CREATE TABLE order_items (
    orderItemId INT AUTO_INCREMENT PRIMARY KEY,
    orderId INT NOT NULL,
    productId INT NOT NULL,
    variantId INT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orderId) REFERENCES orders(orderId) ON DELETE CASCADE,
    FOREIGN KEY (productId) REFERENCES products(productId) ON DELETE RESTRICT,
    FOREIGN KEY (variantId) REFERENCES product_variants(variantId) ON DELETE SET NULL,
    INDEX idx_order (orderId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Appointment Slots Table (available times)
CREATE TABLE appointment_slots (
    slotId INT AUTO_INCREMENT PRIMARY KEY,
    appointmentDate DATE NOT NULL,
    startTime TIME NOT NULL,
    endTime TIME NOT NULL,
    isAvailable BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slot (appointmentDate, startTime),
    INDEX idx_date (appointmentDate),
    INDEX idx_available (isAvailable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Appointments Table (bookings)
CREATE TABLE appointments (
    appointmentId INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    slotId INT NOT NULL,
    designType VARCHAR(100),
    notes TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE RESTRICT,
    FOREIGN KEY (slotId) REFERENCES appointment_slots(slotId) ON DELETE RESTRICT,
    INDEX idx_user (userId),
    INDEX idx_slot (slotId),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Reset Tokens Table
CREATE TABLE password_reset_tokens (
    tokenId INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expiresAt TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user (userId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs Table (for admin monitoring)
CREATE TABLE activity_logs (
    logId INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NULL,
    action VARCHAR(255) NOT NULL,
    entityType VARCHAR(100),
    entityId INT NULL,
    details TEXT,
    ipAddress VARCHAR(45),
    userAgent TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE SET NULL,
    INDEX idx_user (userId),
    INDEX idx_action (action),
    INDEX idx_created (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: Admin123!)
INSERT INTO users (email, password, firstName, lastName, role, emailVerified) VALUES
('admin@nuellasignet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', TRUE);

-- Insert sample appointment slots (next 30 days, 10 AM - 4 PM)
INSERT INTO appointment_slots (appointmentDate, startTime, endTime) VALUES
(CURDATE() + INTERVAL 1 DAY, '10:00:00', '11:00:00'),
(CURDATE() + INTERVAL 1 DAY, '11:00:00', '12:00:00'),
(CURDATE() + INTERVAL 1 DAY, '14:00:00', '15:00:00'),
(CURDATE() + INTERVAL 1 DAY, '15:00:00', '16:00:00'),
(CURDATE() + INTERVAL 2 DAY, '10:00:00', '11:00:00'),
(CURDATE() + INTERVAL 2 DAY, '11:00:00', '12:00:00'),
(CURDATE() + INTERVAL 2 DAY, '14:00:00', '15:00:00'),
(CURDATE() + INTERVAL 2 DAY, '15:00:00', '16:00:00'),
(CURDATE() + INTERVAL 3 DAY, '10:00:00', '11:00:00'),
(CURDATE() + INTERVAL 3 DAY, '11:00:00', '12:00:00'),
(CURDATE() + INTERVAL 3 DAY, '14:00:00', '15:00:00'),
(CURDATE() + INTERVAL 3 DAY, '15:00:00', '16:00:00');

-- Insert sample products
INSERT INTO products (productName, description, price, category, stock, image) VALUES
('Elegant Evening Gown', 'Beautiful evening gown perfect for special occasions', 299.99, 'Evening Wear', 15, '/images/products/evening-gown-1.jpg'),
('Classic Wedding Dress', 'Timeless wedding dress with lace details', 899.99, 'Wedding', 5, '/images/products/wedding-dress-1.jpg'),
('Cocktail Dress', 'Stylish cocktail dress for parties', 149.99, 'Cocktail', 20, '/images/products/cocktail-dress-1.jpg'),
('Business Suit', 'Professional women\'s business suit', 199.99, 'Business', 12, '/images/products/business-suit-1.jpg'),
('Summer Maxi Dress', 'Flowing maxi dress perfect for summer', 89.99, 'Casual', 25, '/images/products/maxi-dress-1.jpg');

-- Insert sample product variants
INSERT INTO product_variants (productId, size, colour, stockQuantity) VALUES
(1, 'S', 'Red', 5),
(1, 'M', 'Red', 5),
(1, 'L', 'Red', 5),
(1, 'S', 'Blue', 4),
(1, 'M', 'Blue', 6),
(2, 'S', 'White', 2),
(2, 'M', 'White', 2),
(2, 'L', 'White', 1),
(3, 'S', 'Black', 8),
(3, 'M', 'Black', 7),
(3, 'L', 'Black', 5),
(4, 'S', 'Navy', 5),
(4, 'M', 'Navy', 4),
(4, 'L', 'Navy', 3),
(5, 'S', 'Floral', 10),
(5, 'M', 'Floral', 10),
(5, 'L', 'Floral', 5);