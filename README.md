# My Fashion Web App

## 1. How To Run

### Requirements
- Docker Desktop
- Git

### Steps
1. Clone the project:

```bash
git clone https://github.com/AmazingNL/My-fashion-webapp.git
cd My-fashion-webapp
```

2. Start everything:

```bash
docker compose up -d --build
```

3. Open:
- App: http://localhost
- phpMyAdmin: http://localhost:8080

This project follows the class Docker setup style:
- `docker-compose.yml` for service orchestration
- `PHP.Dockerfile` for PHP app container
- `nginx.conf` for web server config

## 2. Login Credentials

### Admin account
- Email: admin@nuellasignet.com
- Password: Admin123!

### Customer account
- Email: test@gmail.com
- Password: Customer123!

### Database credentials (Docker)
- Host: mysql (inside Docker network) or localhost:3306 (from host)
- Database: developmentdb
- Username: root
- Password: secret123

## 3. Database Export And Import

### Included export files
- Root export for submission: `lecturer_full_dump.sql`
- Additional copies: `database/lecturer_full_dump.sql`, `database/zzz_full_dump.sql`

### Generate a fresh export
```bash
docker exec my-fashion-webapp-mysql-1 mariadb-dump -uroot -psecret123 --databases developmentdb --routines --events --triggers --single-transaction > lecturer_full_dump.sql
```

### Import export file
```bash
docker exec -i my-fashion-webapp-mysql-1 mariadb -uroot -psecret123 developmentdb < lecturer_full_dump.sql
```

## 4. Architecture, Patterns, And File References

This is a custom PHP MVC project using Controller -> Service -> Repository layering.

### Core MVC flow
- Routing and dispatch: `app/Core/Router.php`
- Base controller helpers/session/flash: `app/Core/ControllerBase.php`
- Auth and role middleware: `app/Core/Middleware.php`

### Layered design
- Controllers (request handling): `app/Controllers/`
- Services (business rules): `app/Services/`
- Repositories (data access): `app/Repositories/`
- Models/entities: `app/Models/`
- View models for page composition: `app/ViewModel/`

### UI rendering pattern
- Server-rendered templates with modular partials under:
	- `app/Views/Admin/partials/`
	- `app/Views/Products/partials/`
	- `app/Views/Cart/partials/`
	- `app/Views/Checkout/partials/`
	- `app/Views/Layouts/partials/`

### Notable implementation points
- CSRF validation on sensitive POST actions: controller-level checks in `app/Controllers/`
- Role-protected admin features: middleware checks in `app/Core/Middleware.php`
- Order status transitions and business rules: `app/Controllers/OrderController.php`, `app/Services/OrderService.php`
- Appointment slot management and monthly slot generation: `app/Controllers/AppointmentController.php`, `app/Services/AppointmentService.php`

## 5. Feature Behavior Summary

### Customer side
- Register/login
- Browse products and variants
- Add/remove/update cart
- Checkout and place orders
- View orders and cancel allowed orders
- Book/edit/cancel appointments

### Admin side
- Dashboard overview
- Product CRUD and variant management
- User management (delete customer user, admin account protected)
- Manage orders and update status
- Manage appointment slots

## 6. GDPR And WCAG Notes

### GDPR efforts
- Data minimization in UI: only necessary account and order data displayed
- Password security: hashed passwords in database
- Session-based authentication and role restriction for protected routes
- Contact/booking/order data processed for service fulfillment purposes only
- Local development email logging to `storage/emails/` for transparency/testing

### WCAG efforts
- Semantic server-rendered HTML structure in view templates
- Form labels and explicit input fields in auth/checkout/appointment flows
- Keyboard-accessible standard controls (links, buttons, inputs)
- Visible validation and status messaging via flash/error notices
- Color/style consistency through CSS files in `public/assets/css/`


## 7. Zip Submission

Submit a `.zip` of the entire project root folder (`My-fashion-webapp`) including:
- source code
- Docker files
- README
- root database export `lecturer_full_dump.sql`


