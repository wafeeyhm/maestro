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

Maestro API ReferenceThis document serves as a detailed reference for all available endpoints in the Maestro API. All requests should be made to the base URL of your application, followed by the specific endpoint path (e.g., http://localhost/maestro-api/api/v1/...).All API interactions are designed to be RESTful, using standard HTTP methods and JSON for data exchange.AuthenticationAll secured endpoints require an access token obtained from the /auth/login or /auth/register endpoints. This token must be included in the request headers with the Authorization key and the Bearer prefix.Request Header Example:Authorization: Bearer mock-token-your_user_id-your_tenant_id1. Authentication EndpointsThese endpoints are used to manage user authentication and issue access tokens.POST /auth/registerUsed to create a new user account for a specific tenant.Request Body:{
  "tenantId": "cafe-brazil",
  "email": "john.doe@example.com",
  "password": "securepassword123",
  "firstName": "John",
  "lastName": "Doe",
  "role": "manager"
}
Success Response: 201 Created{
  "message": "User registered successfully",
  "userId": "68be673b8cddb-user-id",
  "tenantId": "cafe-brazil",
  "token": "mock-token-68be673b8cddb-user-id-cafe-brazil"
}
Error Responses:400 Bad Request: Missing or invalid fields.409 Conflict: Email already exists.POST /auth/loginUsed to authenticate an existing user and retrieve a new access token.Request Body:{
  "email": "john.doe@example.com",
  "password": "securepassword123"
}
Success Response: 200 OK{
  "message": "Login successful",
  "userId": "68be673b8cddb-user-id",
  "tenantId": "cafe-brazil",
  "token": "mock-token-68be673b8cddb-user-id-cafe-brazil"
}
Error Responses:400 Bad Request: Missing email or password.401 Unauthorized: Invalid email or password.2. Products EndpointsThese endpoints are used to manage products. They all require a valid access token.GET /productsRetrieves a list of all products for the authenticated tenant.Success Response: 200 OK[
  {
    "productId": "prod-68be673b8cddb",
    "name": "Iced Matcha Latte",
    "description": "A refreshing iced beverage.",
    "price": "5.99",
    "imageUrl": "[https://example.com/images/iced-matcha.jpg](https://example.com/images/iced-matcha.jpg)",
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
Error Response:401 Unauthorized: Invalid or missing token.GET /products/{productId}Retrieves a single product by its unique ID.Path Parameter:productId: The unique ID of the product.Success Response: 200 OK{
  "productId": "prod-68be673b8cddb",
  "name": "Iced Matcha Latte",
  "description": "A refreshing iced beverage.",
  "baseCost": "2.50",
  "price": "5.99",
  "imageUrl": "[https://example.com/images/iced-matcha.jpg](https://example.com/images/iced-matcha.jpg)",
  "isActive": 1,
  "createdAt": "2023-10-27 10:00:00"
}
Error Response:404 Not Found: Product with the specified ID does not exist.POST /productsCreates a new product.Request Body:{
  "name": "Mocha Frappuccino",
  "description": "A delicious frozen drink.",
  "baseCost": 3.00,
  "price": 6.50,
  "imageUrl": "[https://example.com/images/mocha.jpg](https://example.com/images/mocha.jpg)",
  "categoryId": "CAT-68be673b8cddc"
}
Success Response: 201 Created{
  "message": "Product created successfully",
  "productId": "prod-68be673b8cddf"
}
Error Response:400 Bad Request: Missing or invalid fields.PUT /products/{productId}Updates an existing product. Only include the fields you want to change.Path Parameter:productId: The unique ID of the product to update.Request Body:{
  "price": 6.75,
  "isActive": false
}
Success Response: 200 OK{
  "message": "Product updated successfully"
}
Error Responses:400 Bad Request: Invalid fields provided.404 Not Found: Product with the specified ID does not exist.DELETE /products/{productId}Deletes a product by its unique ID.Path Parameter:productId: The unique ID of the product to delete.Success Response: 200 OK{
  "message": "Product deleted successfully"
}
Error Response:404 Not Found: Product with the specified ID does not exist.
