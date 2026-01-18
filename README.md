# Custom Female Clothing Web App

An e-commerce and booking platform for a custom women’s fashion boutique. Customers can browse products, add items to a cart, place orders, and book design appointments. Admins can manage products, orders, appointments, and user activity.

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

## Notes
- The project is fully Dockerized and can be started with `docker-compose up -d`.
- If you need a ZIP for submission, zip the whole project folder after confirming the database export is present.
