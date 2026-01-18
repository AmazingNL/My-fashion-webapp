# Custom Female Clothing Web App

## Project Description
A boutique e-commerce and appointment-booking site for custom women’s fashion. 
Customers can browse products, add items to a cart, place orders, and book design consultations. 
Admins get a dashboard to manage products, orders, appointments, and activity logs.

## Features
- Product catalog with variants (size/color) and detail pages
- Shopping cart + checkout flow
- Appointment booking with available slot lookup (JSON API)
- Admin dashboard for products, orders, appointments, and activity logs
- Session-based auth and CSRF protection

## Tech Stack
- **Backend:** PHP (custom MVC), FastRoute
- **Database:** MariaDB/MySQL with PDO
- **Frontend:** HTML, CSS, vanilla JavaScript
- **Containerization:** Docker + docker-compose

## Requirements
- **Recommended:** Docker + Docker Compose
- **Manual setup:** PHP 8+, Composer, MariaDB/MySQL, and a web server (Nginx/Apache)

## Installation
### 1) Docker (recommended)
```bash
docker-compose up -d
```

**Database import**
- Open phpMyAdmin at `http://localhost:8080`.
- Log in with **user** `developer` and **password** `secret123`.
- Import `database/schema.sql` into your database.

> **Important:** The app reads database settings from `app/config.php`. That file currently expects:
> - host: `mysql`
> - user: `root`
> - password: `secret123`
> - database: `nuellasignet`
>
> If you’re using the default docker-compose file (which creates `developmentdb`), update **either**:
> - `app/config.php` to match `developmentdb`, **or**
> - `docker-compose.yml` to create `nuellasignet`.

### 2) Manual setup
```bash
composer install
```
- Create a MySQL/MariaDB database and import `database/schema.sql`.
- Update `app/config.php` with your database credentials.
- Point your web server document root at `public/`.

## How to Run
- App: `http://localhost`
- phpMyAdmin: `http://localhost:8080`

## Example Usage
- Browse products: `/productLists`
- View a product: `/products/{id}`
- View cart: `/viewCart`
- Book an appointment: `/appointments/book`
- Admin dashboard: `/admin/dashboard`

## Login Credentials
- **Admin:** `admin@nuellasignet.com` / `Admin123!` (seeded in `database/schema.sql`)
- **Customer:** register a new account via `/showRegistrationForm`

## Folder Structure
```
.
├── app/
│   ├── Controllers/
│   ├── core/
│   ├── models/
│   ├── repositories/
│   ├── services/
│   └── Views/
├── public/
│   ├── assets/
│   ├── images/
│   └── index.php
├── database/
│   └── schema.sql
├── docker-compose.yml
├── PHP.Dockerfile
└── nginx.conf
```

## Design Choices
- **Custom MVC**: Controllers, models, views, and routing are separated for clarity (`app/Controllers`, `app/models`, `app/Views`, `app/core/Router.php`).
- **Service + Repository layers**: Business logic is kept in services, data access in repositories (`app/services`, `app/repositories`). This keeps controllers thin and easier to test.
- **Shared PDO connection**: `app/core/RepositoryBase.php` manages a single shared database connection.
- **Server-rendered views**: PHP templates in `app/Views` keep the UI simple and fast.

## Accessibility & GDPR Notes
- **WCAG basics**: Semantic HTML, labeled form inputs, and image alt text are used across views (see `app/Views`).
- **GDPR basics**: Minimal user data is stored, passwords are hashed, and CSRF tokens are used (see `app/core/ControllerBase.php`).

## Database Export
A full database export (schema + seed data) is included at:
```
/database/schema.sql
```

## Database Structure (Full Schema)
```sql
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
```

## Notes
- The project is fully Dockerized and can be started with `docker-compose up -d`.
