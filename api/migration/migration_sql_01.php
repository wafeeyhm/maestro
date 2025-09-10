<?php

// --- Database Configuration ---
$servername = "localhost";
$username = "root"; // CHANGE THIS to your MySQL username
$password = "";     // CHANGE THIS to your MySQL password
$dbname = "maestro";

// --- Create Connection ---
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Create the Database if it doesn't exist ---
$sql_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_db) === TRUE) {
    echo "Database '$dbname' created successfully or already exists.\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

// Select the database
$conn->select_db($dbname);

// --- SQL Statements to Create Tables ---

// 1. Create 'tenants' table
$sql_tenants = "CREATE TABLE IF NOT EXISTS tenants (
    tenantId VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    ownerId VARCHAR(255) NOT NULL,
    createdAt DATETIME NOT NULL,
    isActive BOOLEAN NOT NULL
)";
if ($conn->query($sql_tenants) === TRUE) {
    echo "Table 'tenants' created successfully or already exists.\n";
} else {
    echo "Error creating table 'tenants': " . $conn->error . "\n";
}

// 2. Create 'users' table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    userId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    firstName VARCHAR(255) NOT NULL,
    lastName VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    createdAt DATETIME NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId)
)";
if ($conn->query($sql_users) === TRUE) {
    echo "Table 'users' created successfully or already exists.\n";
} else {
    echo "Error creating table 'users': " . $conn->error . "\n";
}

// 3. Create 'product_categories' table
$sql_product_categories = "CREATE TABLE IF NOT EXISTS product_categories (
    categoryId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    createdAt DATETIME NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId)
)";
if ($conn->query($sql_product_categories) === TRUE) {
    echo "Table 'product_categories' created successfully or already exists.\n";
} else {
    echo "Error creating table 'product_categories': " . $conn->error . "\n";
}

// 4. Create 'products' table
$sql_products = "CREATE TABLE IF NOT EXISTS products (
    productId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    categoryId VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    baseCost DECIMAL(10, 2) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    imageUrl VARCHAR(255),
    isActive BOOLEAN NOT NULL,
    createdAt DATETIME NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId),
    FOREIGN KEY (categoryId) REFERENCES product_categories(categoryId)
)";
if ($conn->query($sql_products) === TRUE) {
    echo "Table 'products' created successfully or already exists.\n";
} else {
    echo "Error creating table 'products': " . $conn->error . "\n";
}

// 5. Create 'inventory' table
$sql_inventory = "CREATE TABLE IF NOT EXISTS inventory (
    itemId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    productId VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit VARCHAR(50) NOT NULL,
    lastUpdated DATETIME NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId),
    FOREIGN KEY (productId) REFERENCES products(productId)
)";
if ($conn->query($sql_inventory) === TRUE) {
    echo "Table 'inventory' created successfully or already exists.\n";
} else {
    echo "Error creating table 'inventory': " . $conn->error . "\n";
}

// 6. Create 'customers' table
$sql_customers = "CREATE TABLE IF NOT EXISTS customers (
    customerId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    firstName VARCHAR(255) NOT NULL,
    lastName VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    notes TEXT,
    createdAt DATETIME NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId)
)";
if ($conn->query($sql_customers) === TRUE) {
    echo "Table 'customers' created successfully or already exists.\n";
} else {
    echo "Error creating table 'customers': " . $conn->error . "\n";
}

// 7. Create 'orders' table
$sql_orders = "CREATE TABLE IF NOT EXISTS orders (
    orderId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    userId VARCHAR(255) NOT NULL,
    customerId VARCHAR(255),
    status VARCHAR(50) NOT NULL,
    totalAmount DECIMAL(10, 2) NOT NULL,
    paymentMethod VARCHAR(50) NOT NULL,
    createdAt DATETIME NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId),
    FOREIGN KEY (userId) REFERENCES users(userId),
    FOREIGN KEY (customerId) REFERENCES customers(customerId)
)";
if ($conn->query($sql_orders) === TRUE) {
    echo "Table 'orders' created successfully or already exists.\n";
} else {
    echo "Error creating table 'orders': " . $conn->error . "\n";
}

// 8. Create 'order_items' table
$sql_order_items = "CREATE TABLE IF NOT EXISTS order_items (
    orderItemId VARCHAR(255) PRIMARY KEY,
    orderId VARCHAR(255) NOT NULL,
    productId VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    priceAtSale DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (orderId) REFERENCES orders(orderId),
    FOREIGN KEY (productId) REFERENCES products(productId)
)";
if ($conn->query($sql_order_items) === TRUE) {
    echo "Table 'order_items' created successfully or already exists.\n";
} else {
    echo "Error creating table 'order_items': " . $conn->error . "\n";
}

// 9. Create 'sales_records' table
$sql_sales_records = "CREATE TABLE IF NOT EXISTS sales_records (
    recordId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    orderId VARCHAR(255) NOT NULL,
    totalAmount DECIMAL(10, 2) NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId),
    FOREIGN KEY (orderId) REFERENCES orders(orderId)
)";
if ($conn->query($sql_sales_records) === TRUE) {
    echo "Table 'sales_records' created successfully or already exists.\n";
} else {
    echo "Error creating table 'sales_records': " . $conn->error . "\n";
}

// 10. Create 'financial_records' table
$sql_financial_records = "CREATE TABLE IF NOT EXISTS financial_records (
    recordId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    date DATETIME NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId)
)";
if ($conn->query($sql_financial_records) === TRUE) {
    echo "Table 'financial_records' created successfully or already exists.\n";
} else {
    echo "Error creating table 'financial_records': " . $conn->error . "\n";
}

// 11. Create 'audit_logs' table
$sql_audit_logs = "CREATE TABLE IF NOT EXISTS audit_logs (
    logId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    userId VARCHAR(255) NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT NOT NULL,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId),
    FOREIGN KEY (userId) REFERENCES users(userId)
)";
if ($conn->query($sql_audit_logs) === TRUE) {
    echo "Table 'audit_logs' created successfully or already exists.\n";
} else {
    echo "Error creating table 'audit_logs': " . $conn->error . "\n";
}

// 12. Create 'ingredients' table
$sql_ingredients = "CREATE TABLE IF NOT EXISTS ingredients (
    ingredientId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    unitCost DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    supplier VARCHAR(255),
    createdAt DATETIME NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId)
)";
if ($conn->query($sql_ingredients) === TRUE) {
    echo "Table 'ingredients' created successfully or already exists.\n";
} else {
    echo "Error creating table 'ingredients': " . $conn->error . "\n";
}

// 13. Create 'product_recipes' table
$sql_product_recipes = "CREATE TABLE IF NOT EXISTS product_recipes (
    recipeId VARCHAR(255) PRIMARY KEY,
    tenantId VARCHAR(255) NOT NULL,
    productId VARCHAR(255) NOT NULL,
    ingredientId VARCHAR(255) NOT NULL,
    quantityRequired DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (tenantId) REFERENCES tenants(tenantId),
    FOREIGN KEY (productId) REFERENCES products(productId),
    FOREIGN KEY (ingredientId) REFERENCES ingredients(ingredientId)
)";
if ($conn->query($sql_product_recipes) === TRUE) {
    echo "Table 'product_recipes' created successfully or already exists.\n";
} else {
    echo "Error creating table 'product_recipes': " . $conn->error . "\n";
}

// --- Close Connection ---
$conn->close();

?>
