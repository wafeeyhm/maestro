Maestro APIA robust, multi-tenant RESTful API designed to power e-commerce and point-of-sale applications for cafes and restaurants. The API provides a secure, scalable backend for managing products, inventory, sales, and more, serving as the "single source of truth" for business operations.Key FeaturesMulti-Tenancy: Each piece of data is associated with a unique tenantId, ensuring secure data isolation for every cafe.RESTful Endpoints: Standard HTTP methods (GET, POST, PUT, DELETE) for all CRUD operations.Robust Architecture: A clear MVC (Model-View-Controller) pattern with a single front controller (index.php) for all request routing.Data Security: All database interactions use prepared statements to prevent SQL injection.Project StructureThe project follows a clean and logical directory structure:maestro-api/
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
└── database.sql
└── README.md
PrerequisitesTo get started with this project, you need to have the following installed:PHP (version 7.4 or higher)A Web Server (Apache, Nginx, etc.)A MySQL or MariaDB databaseInstallation & Setup1. Database SetupFirst, you need to create the database and tables.Create a new database named maestro in your MySQL server.Import the schema by running the SQL commands from the database.sql file.2. Configure Database ConnectionNext, update your database credentials in the api/config/database.php file:// api/config/database.php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_username'); // CHANGE THIS
define('DB_PASSWORD', 'your_password'); // CHANGE THIS
define('DB_NAME', 'maestro');
API EndpointsAll endpoints are accessed through the index.php front controller. The base URL will depend on your local server configuration (e.g., http://localhost/maestro-api/api/).Products ResourceEndpointMethodDescription/productsGETRetrieves all products for a specific tenant./products/{productId}GETRetrieves a single product by its unique ID./productsPOSTCreates a new product./products/{productId}PUTUpdates an existing product./products/{productId}DELETEDeletes a product.Examples1. Get All Productscurl -X GET "http://localhost/maestro-api/api/products"
2. Create a New Productcurl -X POST "http://localhost/maestro-api/api/products" \
-H "Content-Type: application/json" \
-d '{
    "name": "Iced Matcha Latte",
    "description": "A refreshing iced beverage.",
    "baseCost": "2.50",
    "price": "5.99",
    "imageUrl": "[https://example.com/images/iced-matcha.jpg](https://example.com/images/iced-matcha.jpg)",
    "tenantId": "your_tenant_id",
    "categoryId": "2"
}'
3. Update a Productcurl -X PUT "http://localhost/maestro-api/api/products/651a2e3a5f4d1" \
-H "Content-Type: application/json" \
-d '{"price": "6.25"}'
