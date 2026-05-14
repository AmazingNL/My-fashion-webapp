# My Fashion Web App

Fashion boutique web application with a custom PHP JSON API backend and a Vue/Vite frontend. The app supports customer shopping, carts, favourites, checkout, appointment booking, user settings, admin management, Mailtrap email delivery/logging, and Stripe/PayPal payment flows.

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

The Docker/nginx API is served from the root host, for example `http://localhost/products`. The Vue development client can call the API through its `/api` proxy configuration.

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

### Mailtrap email service
Copy `.env.example` to `.env`, then add your Mailtrap values:

```env
MAIL_ENABLED=true
MAIL_MAILER=mailtrap
MAIL_FROM_EMAIL=noreply@nuellasignet.com
MAIL_FROM_NAME="Nuella Signet"
MAILTRAP_MODE=sandbox
MAILTRAP_API_TOKEN=your_mailtrap_api_token
MAILTRAP_INBOX_ID=your_mailtrap_sandbox_inbox_id
EMAIL_LOG_ENABLED=true
```

Use `MAILTRAP_MODE=sandbox` to view test emails inside your Mailtrap sandbox inbox. For production sending through Mailtrap Email Sending, use:

```env
MAILTRAP_MODE=production
MAILTRAP_API_TOKEN=your_mailtrap_sending_api_token
MAIL_FROM_EMAIL=verified-sender@yourdomain.com
```

When `EMAIL_LOG_ENABLED=true`, the app also keeps local copies in `storage/emails/`, which admins can view from `/admin/emails`.

### Payment service
Stripe and PayPal are optional but supported for checkout. Add test credentials to `.env` before using online payments:

```env
PAYMENT_CURRENCY=eur
STRIPE_SECRET_KEY=sk_test_your_stripe_secret_key
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_MODE=sandbox
```

Bank transfer checkout works without external payment credentials. Stripe and PayPal create provider checkout sessions/orders, then the app confirms payment through `POST /checkout/payments/confirm` and sends the order confirmation email.

## 3. Database Export And Import

### Included export files
- Root export: `lecturer_full_dump.sql`

### Generate a fresh export (UTF-8 SQL)
```bash
docker compose exec -T mysql mariadb-dump \
	-uroot -psecret123 \
	--databases developmentdb \
	--routines --events --triggers \
	--single-transaction \
	--default-character-set=utf8mb4 \
	> lecturer_full_dump.sql
```

### Import export file
```bash
iconv -f UTF-16LE -t UTF-8 lecturer_full_dump.sql > lecturer_full_dump_utf8.sql
docker compose exec -T mysql mariadb -uroot -psecret123 developmentdb < lecturer_full_dump_utf8.sql
```

### Verify data loaded
```bash
docker compose exec -T mysql mariadb -uroot -psecret123 -e "
USE developmentdb;
SHOW TABLES;
SELECT 'users' AS table_name, COUNT(*) AS rows_count FROM users
UNION ALL SELECT 'products', COUNT(*) FROM products
UNION ALL SELECT 'orders', COUNT(*) FROM orders;
"
```

## 4. Architecture, Patterns, And File References

This is a custom PHP API project using Controller -> Service -> Repository layering.

### Core API flow
- Routing and dispatch: `app/Core/Router.php`
- Base controller JSON helpers: `app/Core/ControllerBase.php`
- Auth and role middleware: `app/Core/Middleware.php`

### Layered design
- Controllers (request handling): `app/Controllers/`
- Services (business rules): `app/Services/`
- Repositories (data access): `app/Repositories/`
- Models/entities: `app/Models/`
- DTOs (API request/response shapes): `app/DTO/`
- Mappers (model/DTO conversion): `app/Mappers/`

### Frontend split
- PHP exposes JSON endpoints for the Vue frontend.
- Vue/Vite frontend source lives in `frontend/`.
- Server-rendered PHP views have been removed from this version.
- Postman resources are included for API testing and workflow checks.

### Notable implementation points
- JWT authentication and JSON responses for protected actions
- Role-protected admin features: middleware checks in `app/Core/Middleware.php`
- Order status transitions and business rules: `app/Controllers/OrderController.php`, `app/Services/OrderService.php`
- Appointment slot management and monthly slot generation: `app/Controllers/AppointmentController.php`, `app/Services/AppointmentService.php`
- Theme persistence: `frontend/src/stores/themeStore.js`
- Local favourite hearts: `frontend/src/stores/favouriteStore.js`
- Email sending and local email logs: `app/Services/EmailService.php`, `app/Services/EmailLogService.php`
- Stripe/PayPal integration: `app/Services/PaymentService.php`, `app/Controllers/CheckoutController.php`

## 5. Feature Behavior Summary

### Customer side
- Register/login
- Forgot password with email reset code
- Browse products and variants
- Save favourite products with heart buttons
- Add/remove/update cart items
- Checkout with bank transfer, Stripe, or PayPal
- View orders and cancel allowed orders
- Book/edit/cancel appointments
- User settings page with bright and dark theme selection

### Admin side
- Dashboard overview with split management workspaces
- Product CRUD and variant management
- User management (delete customer user, admin account protected)
- Manage orders, view order items, and update status
- Manage appointment slots and appointment status
- View locally logged emails from the email service dashboard

## 6. Important Endpoints

### Public and auth
- `GET /about`
- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/password-reset`
- `POST /auth/password-reset/verify`

### Customer
- `GET /products`
- `GET /products/{id}`
- `GET /cart`
- `POST /cart/items`
- `PATCH /cart/items`
- `DELETE /cart`
- `GET /checkout`
- `POST /checkout`
- `POST /checkout/payments/confirm`
- `GET /checkout/confirmation/{orderId}`
- `GET /orders`
- `GET /orders/{orderId}`
- `GET /favourites`
- `DELETE /favourites`
- `GET /appointments`
- `GET /appointments/slots?date=YYYY-MM-DD`

### Admin
- `GET /admin/dashboard`
- `GET /admin/users`
- `GET /admin/products`
- `GET /admin/products/{id}`
- `POST /admin/products`
- `POST /admin/products/{id}`
- `DELETE /admin/products/{id}`
- `GET /admin/orders`
- `GET /admin/orders/{orderId}/items`
- `PATCH /admin/orders/{orderId}/status`
- `GET /admin/appointments`
- `POST /admin/appointments/slots`
- `PATCH /admin/appointments/{appointmentId}/status`
- `GET /admin/emails`
- `GET /admin/emails/{fileName}`

## 7. Endpoint Smoke Test Status

The current Docker environment was smoke-tested against `http://localhost` with customer and admin credentials. The test covered product browsing, login, cart, checkout, order confirmation, favourites, password reset request, appointments, admin dashboard, admin products, admin orders, admin appointments, and admin email logs. Result: 27/27 tested endpoints passed.

## 8. GDPR And WCAG Notes

### GDPR efforts
- Data minimization in UI: only necessary account and order data displayed
- Password security: hashed passwords in database
- JWT-based authentication and role restriction for protected routes
- Contact/booking/order data processed for service fulfillment purposes only
- Local development email logging to `storage/emails/` for transparency/testing

### WCAG efforts
- Frontend accessibility is handled in the separate Vue client.
- The API returns structured validation and status responses for frontend messaging.


## 9. Zip Submission

Submit a `.zip` of the entire project root folder (`My-fashion-webapp`) including:
- source code
- Docker files
- README
- root database export `lecturer_full_dump.sql`


