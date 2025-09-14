# Maestro API

A robust, multi-tenant RESTful API designed to power e-commerce and point-of-sale applications for cafes and restaurants. The API provides a secure, scalable backend for managing products, orders, users, and authentication.

---

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

---

## Prerequisites

To get started with this project, you need to have the following installed:

- **PHP** (version 7.4 or higher)
- **A Web Server** (Apache, Nginx, etc.)
- **A MySQL or MariaDB database**

---

## Installation & Setup

1. **Download or clone this repository.**

2. **Configure your database connection:**

   Edit `api/config/database.php`:

   ```php
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'your_username'); // CHANGE THIS
   define('DB_PASSWORD', 'your_password'); // CHANGE THIS
   define('DB_NAME', 'maestro');
   ```

3. **Import the database schema:**

   ```sh
   mysql -u your_username -p maestro < database.sql
   ```

4. **Set up your web server** to serve files from the `api/` directory or configure your virtual host accordingly.

---

## API Endpoints

All endpoints are accessed through the `index.php` front controller. The base URL will depend on your local server configuration (e.g., `http://localhost/maestro-api/api/`).

---

### 1. Authentication

#### Register a New User

**POST** `/auth/register`

Request Body:

```json
{
  "tenantId": "cafe-brazil",
  "email": "john.doe@example.com",
  "password": "securepassword123",
  "firstName": "John",
  "lastName": "Doe",
  "role": "manager"
}
```

Success Response: `201 Created`

```json
{
  "message": "User registered successfully",
  "userId": "68be673b8cddb-user-id",
  "tenantId": "cafe-brazil",
  "token": "mock-token-68be673b8cddb-user-id-cafe-brazil"
}
```

Error Responses:

- `400 Bad Request`: Missing or invalid fields.
- `409 Conflict`: Email already exists.

#### Login

**POST** `/auth/login`

Request Body:

```json
{
  "email": "john.doe@example.com",
  "password": "securepassword123"
}
```

Success Response: `200 OK`

```json
{
  "message": "Login successful",
  "userId": "68be673b8cddb-user-id",
  "tenantId": "cafe-brazil",
  "token": "mock-token-68be673b8cddb-user-id-cafe-brazil"
}
```

Error Responses:

- `400 Bad Request`: Missing email or password.
- `401 Unauthorized`: Invalid email or password.

---

### 2. Products Endpoints

All product endpoints require a valid access token (passed via `Authorization` header).

#### List All Products

**GET** `/products`

Sample Request:

```sh
curl "http://localhost/maestro-api/api/products"
```

Success Response: `200 OK`

```json
[
  {
    "productId": "prod-68be673b8cddb",
    "name": "Iced Matcha Latte",
    "description": "A refreshing iced beverage.",
    "price": "5.99",
    "imageUrl": "https://example.com/images/iced-matcha.jpg",
    "categoryId": "CAT-68be673b8cddc"
  },
  {
    "productId": "prod-abc12345",
    "name": "Hot Coffee",
    "description": "Brewed daily.",
    "price": "2.50",
    "imageUrl": null,
    "categoryId": "CAT-ab123456"
  }
]
```

Error Response:

- `401 Unauthorized`: Invalid or missing token.

---

#### Get a Single Product

**GET** `/products/{productId}`

Path Parameter:

- `productId`: The unique ID of the product.

Success Response: `200 OK`

```json
{
  "productId": "prod-68be673b8cddb",
  "name": "Iced Matcha Latte",
  "description": "A refreshing iced beverage.",
  "baseCost": "2.50",
  "price": "5.99",
  "imageUrl": "https://example.com/images/iced-matcha.jpg",
  "isActive": 1,
  "createdAt": "2023-10-27 10:00:00"
}
```

Error Response:

- `404 Not Found`: Product with the specified ID does not exist.

---

#### Create a New Product

**POST** `/products`

Sample Request:

```sh
curl -X POST "http://localhost/maestro-api/api/products" \
-H "Content-Type: application/json" \
-d '{
  "name": "Mocha Frappuccino",
  "description": "A delicious frozen drink.",
  "baseCost": 3.00,
  "price": 6.50,
  "imageUrl": "https://example.com/images/mocha.jpg",
  "categoryId": "CAT-68be673b8cddc"
}'
```

Success Response: `201 Created`

```json
{
  "message": "Product created successfully",
  "productId": "prod-68be673b8cddf"
}
```

Error Response:

- `400 Bad Request`: Missing or invalid fields.

---

#### Update a Product

**PUT** `/products/{productId}`

Path Parameter:

- `productId`: The unique ID of the product.

Sample Request:

```sh
curl -X PUT "http://localhost/maestro-api/api/products/651a2e3a5f4d1" \
-H "Content-Type: application/json" \
-d '{"price": "6.25"}'
```

Request Body: Only include the fields you want to change.

Example:

```json
{
  "price": 6.75,
  "isActive": false
}
```

Success Response: `200 OK`

```json
{
  "message": "Product updated successfully"
}
```

Error Responses:

- `400 Bad Request`: Invalid fields provided.
- `404 Not Found`: Product with the specified ID does not exist.

---

#### Delete a Product

**DELETE** `/products/{productId}`

Path Parameter:

- `productId`: The unique ID of the product.

Success Response: `200 OK`

```json
{
  "message": "Product deleted successfully"
}
```

Error Response:

- `404 Not Found`: Product with the specified ID does not exist.

---

## Notes

- All requests and responses use `application/json` unless otherwise specified.
- Be sure to include your access token (from `/auth/login` or `/auth/register`) in the `Authorization` header for protected endpoints.

---

## License

MIT
