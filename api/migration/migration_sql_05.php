<?php

// --- Database Configuration ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "maestro";

// --- Create Connection ---
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create the sales and sales_items tables
$sql = "
-- Create the 'sales' table to store general transaction information
CREATE TABLE IF NOT EXISTS `sales` (
    `saleId` VARCHAR(50) PRIMARY KEY,
    `tenantId` VARCHAR(50) NOT NULL,
    `userId` VARCHAR(50) NOT NULL,
    `customerId` VARCHAR(50),
    `paymentMethod` VARCHAR(50) NOT NULL,
    `totalAmount` DECIMAL(10, 2) NOT NULL,
    `items` LONGTEXT,
    `createdAt` DATETIME NOT NULL
);

-- Create the 'sales_items' table to store details for each product in a sale
CREATE TABLE IF NOT EXISTS `sales_items` (
    `itemId` VARCHAR(50) PRIMARY KEY,
    `saleId` VARCHAR(50) NOT NULL,
    `productId` VARCHAR(50) NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `createdAt` DATETIME NOT NULL,
    FOREIGN KEY (`saleId`) REFERENCES `sales`(`saleId`),
    FOREIGN KEY (`productId`) REFERENCES `products`(`productId`)
);
";

if ($conn->multi_query($sql) === TRUE) {
    echo "Tables created successfully.\n";
} else {
    echo "Error creating tables: " . $conn->error . "\n";
}

// --- Close Connection ---
$conn->close();

?>
