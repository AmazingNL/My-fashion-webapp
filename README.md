# My Fashion Web App

## 1. How To Download And Run (First)

### Requirements
- Docker Desktop
- Git

### Steps
1. Clone the project

```bash
git clone <your-repo-url>
cd My-fashion-webapp
```

2. Start everything

```bash
docker compose up -d --build
```

3. Open in browser
- App: http://localhost
- phpMyAdmin: http://localhost:8080

### Default admin login
- Email: admin@nuellasignet.com
- Password: Admin123!

### Default customer login
- Email: test@gmail.com
- Password: test12345

## 2. Project Explanation

This project is a custom female fashion web application.

Main idea:
- Customers can browse products, view details, add to cart, checkout, and track orders.
- Customers can also book appointment slots.
- Admin can manage products, users, orders, and appointment slots.

## 3. Architecture (Simple)

This project uses a custom PHP MVC structure with service and repository layers.

- Controllers: handle HTTP requests and responses
- Services: business logic
- Repositories: database queries
- Views: server-rendered PHP templates
- Core: router, base controller, middleware, base repository

Main folders:
- app/Controllers
- app/Services
- app/Repositories
- app/Views
- app/Core
- database
- public

## 4. Functions And Behavior

### Customer side
- Register and login
- Browse products and variants
- Add to cart and update quantity
- Checkout and place orders
- View and cancel allowed orders
- Book, edit, and cancel appointments

### Admin side
- Dashboard summary
- Product CRUD and variant management
- Delete customer users (admin user protected)
- Change order status with transition rules
- Add single appointment slots

### Security behavior
- CSRF token on important POST forms
- Passwords are hashed
- Role checks for admin/customer routes

## 5. Database Notes

- Database is created by Docker on first run.
- SQL files in the database folder are used for initialization.
- If you want a full snapshot with data for another machine, use:

```bash
docker exec my-fashion-webapp-mysql-1 mariadb-dump -uroot -psecret123 --databases developmentdb > database/lecturer_full_dump.sql
```

To import on another machine:

```bash
docker exec -i my-fashion-webapp-mysql-1 mariadb -uroot -psecret123 < database/lecturer_full_dump.sql
```


