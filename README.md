# Maestro API

A robust, multi-tenant RESTful API designed to power e-commerce and point-of-sale applications for cafes and restaurants. The API provides a secure, scalable backend for managing products, orders, users, and more.

## Project Structure

```
├── api/
│   ├── config/
│   │   └── database.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   └── ProductsController.php
│   ├── models/
│   │   ├── LogModel.php
│   │   └── ProductModel.php
│   └── index.php
├── database.sql
└── README.md
```

## Prerequisites

To get started with this project, you need to have the following installed:
- **PHP** (version 7.4 or higher)
- **A Web Server** (Apache, Nginx, etc.)
- **A MySQL or MariaDB database**

## Installation & Setup

1. Download or clone this repository.
2. Configure your database connection:

Edit `api/config/database.php`:

```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_username'); // CHANGE THIS
define('DB_PASSWORD', 'your_password'); // CHANGE THIS
define('DB_NAME', 'maestro');
```

3. Import the database schema:

```sh
mysql -u your_username -p maestro < database.sql
```

4. Set up your web server to serve files from the `api/` directory or configure your virtual host accordingly.

## API Endpoints

All endpoints are accessed through the `index.php` front controller. The base URL will depend on your local server configuration (e.g., `http://localhost/maestro-api/api/`).

### Products Resource

#### 1. List All Products

```sh
curl "http://localhost/maestro-api/api/products"
```

#### 2. Create a New Product

```sh
curl -X POST "http://localhost/maestro-api/api/products" \
-H "Content-Type: application/json" \
-d '{
    "name": "Iced Matcha Latte",
    "description": "A refreshing iced beverage.",
    "baseCost": "2.50",
    "price": "5.99",
    "imageUrl": "https://example.com/images/iced-matcha.jpg",
    "tenantId": "your_tenant_id",
    "categoryId": "2"
}'
```

#### 3. Update a Product

```sh
curl -X PUT "http://localhost/maestro-api/api/products/651a2e3a5f4d1" \
-H "Content-Type: application/json" \
-d '{"price": "6.25"}'
```

---

*Feel free to contribute or open an issue for questions and feature requests!*
