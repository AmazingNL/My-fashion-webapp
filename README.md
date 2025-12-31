# Custom Female Clothing Web Application

**Student:** Amazinggrace Iruoma  
**Student Number:** 726138  
**Course:** Web Development 1  
**Term:** 2.2

## Project Description

This web application is an e-commerce platform for custom female clothing that integrates:
- Product catalog with shopping cart functionality
- Appointment booking system for custom designs (wedding gowns, special occasion dresses)
- User authentication and order management
- Admin panel for product and appointment management

## Technical Stack

- **Backend:** PHP 8+ with MVC architecture
- **Database:** MySQL with PDO
- **Frontend:** HTML5, CSS3, JavaScript
- **Routing:** FastRoute
- **Security:** CSRF protection, XSS prevention, password hashing
- **API:** RESTful JSON endpoints

## Project Structure

```
my-fashion-app/
├── app/
│   ├── Controllers/       # Request handling
│   ├── Models/           # Data models
│   ├── Repositories/     # Data access layer
│   ├── Services/         # Business logic
│   ├── Views/            # HTML templates
│   └── Core/             # Framework core (Router, ControllerBase)
├── Public/
│   ├── assets/           # CSS and JavaScript
│   ├── images/           # Product images
│   └── index.php         # Entry point
└── vendor/               # Composer dependencies
```

## Features Implemented

### Customer Features
- Browse products with filtering and search
- View product details with size/color variants
- Shopping cart (session-based)
- User registration and login
- Appointment booking for custom designs
- Order history

### Admin Features
- Product management (CRUD operations)
- Appointment management
- User management

## WCAG 2.1 Accessibility Compliance

This application implements the following accessibility features to comply with WCAG 2.1 Level AA:

### Perceivable
1. **Text Alternatives (1.1.1)**
   - All images have descriptive alt attributes
   - Example: `ProductDetails.php` line 26
   ```php
   <img src="..." alt="<?= htmlspecialchars($product->getName()); ?>">
   ```

2. **Info and Relationships (1.3.1)**
   - Semantic HTML5 elements used throughout
   - Example: `<article>`, `<section>`, `<nav>` in `ProductDetails.php`
   - Proper heading hierarchy (h1, h2, h3)

3. **Contrast (1.4.3)**
   - Color contrast ratios meet WCAG AA standards
   - CSS files: `productDetails.css`, etc.

### Operable
1. **Keyboard Accessible (2.1.1)**
   - All interactive elements are keyboard accessible
   - Proper tab order maintained
   - Focus indicators visible

2. **Page Titled (2.4.2)**
   - All pages have descriptive titles
   - Example: `ProductController.php` passes title to views
   ```php
   $this->render('Products/ProductDetails', [
       'title' => $product->getName(),
       // ...
   ]);
   ```

3. **Labels or Instructions (3.3.2)**
   - Form fields have associated labels
   - Example: `ProductDetails.php` lines 62-83
   ```php
   <label for="size">Size</label>
   <select id="size" name="size" required>
   ```

### Understandable
1. **Language of Page (3.1.1)**
   - HTML lang attribute set in main layout
   - File: `Views/Layouts/main.php`

2. **On Input (3.2.2)**
   - Forms don't auto-submit on input
   - Explicit submit buttons required

3. **Error Identification (3.3.1)**
   - Server-side validation with clear error messages
   - Example: `ProductService.php` validation

### Robust
1. **Parsing (4.1.1)**
   - Valid HTML5 markup
   - No duplicate IDs
   - Properly nested elements

2. **Name, Role, Value (4.1.2)**
   - ARIA labels for complex interactive elements
   - Example: `ProductDetails.php` line 29
   ```php
   <button ... aria-label="Add to favourites" aria-pressed="false">
   ```

## GDPR Compliance

This application implements the following measures for GDPR compliance:

### 1. Data Minimization (Article 5.1.c)
**Implementation:** Only essential user data is collected:
- Email, name, and hashed password for authentication
- Order details only when purchases are made
- No unnecessary tracking or analytics

**Code Reference:** `Models/User.php`, `Repositories/UserRepository.php`

### 2. Security of Processing (Article 32)
**Implementation:**
- Password hashing using PHP's `password_hash()` with bcrypt
- Parameterized SQL queries to prevent injection
- CSRF token protection on forms
- Input validation and output sanitization

**Code References:**
- `UserService.php` - password hashing
- All repository classes - parameterized queries
- `ControllerBase.php` lines 58-82 - CSRF protection
- `ProductController.php` - input validation

### 3. Right to Access (Article 15)
**Implementation:** Users can view their:
- Profile information
- Order history
- Appointment bookings

**Code Reference:** `UserController.php`, `ProductController.php`

### 4. Right to Erasure (Article 17)
**Implementation:** 
- Users can delete their accounts (soft delete maintaining order integrity)
- Account deletion removes personal data while preserving transaction records for legal compliance

**Code Reference:** `UserRepository.php` delete functionality

### 5. Data Breach Notification (Article 33-34)
**Implementation:**
- Error logging for security monitoring
- Session management with proper expiration
- Secure session cookie flags (when deployed with HTTPS)

**Code Reference:** `ControllerBase.php` session handling

### 6. Privacy by Design (Article 25)
**Implementation:**
- Minimal data collection from the start
- Session-based cart (no tracking before account creation)
- Optional account creation for browsing

**Code Reference:** Session-based features in `ProductService.php`

## Security Features

### XSS Prevention
- All user input is sanitized using `htmlspecialchars()` with `ENT_QUOTES`
- Example: All view files escape output

### SQL Injection Prevention
- PDO prepared statements used throughout
- Example: `ProductRepository.php`, all query methods

### CSRF Protection
- CSRF tokens on all state-changing forms
- Token validation in `ControllerBase.php` lines 65-82

### File Upload Security
- File type validation (MIME type checking)
- File size limits
- Random filename generation
- Example: `ProductController.php` lines 68-108

### Password Security
- Passwords hashed using bcrypt
- Password verification with timing-safe comparison
- Example: `UserService.php`

## API Documentation

### Endpoint: GET /api/products/{id}
Returns product details in JSON format.

**Response:**
```json
{
  "product": {
    "productId": 1,
    "name": "Evening Gown",
    "description": "...",
    "price": 299.99,
    "category": "Formal",
    "image": "/images/products/..."
  },
  "variants": [
    {
      "variantId": 1,
      "size": "M",
      "color": "Red",
      "stock": 5
    }
  ],
  "sizes": ["S", "M", "L"],
  "colors": ["Red", "Blue"],
  "similarProducts": [...]
}
```

**Implementation:** `ProductController.php` lines 218-255

## Database Schema

### Products Table
- productId (PK)
- productName
- description
- price
- category
- stock
- image
- createdAt
- updatedAt
- isActive

### ProductVariants Table
- variantId (PK)
- productId (FK)
- size
- colour
- stockQuantity

### Users Table
- userId (PK)
- email (unique)
- password (hashed)
- firstName
- lastName
- createdAt

### Orders Table
- orderId (PK)
- userId (FK)
- totalAmount
- status
- createdAt

## Installation & Setup

1. **Requirements:**
   - PHP 8.0 or higher
   - MySQL 5.7 or higher
   - Composer

2. **Setup:**
   ```bash
   composer install
   # Configure database connection in app/Config.php
   # Import database schema
   ```

3. **Docker (Alternative):**
   ```bash
   docker-compose up -d
   ```

## Testing

- Unit tests for services and repositories
- Integration tests for API endpoints
- Manual testing for UI/UX

## Known Issues & Future Improvements

- [ ] Add email confirmation for registration
- [ ] Implement password reset functionality
- [ ] Add product reviews
- [ ] Implement real-time appointment calendar
- [ ] Add payment gateway integration

## License

Educational project - All rights reserved

## Contact

Student: Amazinggrace Iruoma  
Email: 726138@student.inholland.nl 
Student Number: 726138