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

// --- Data to be inserted ---
$categoryId = 'CAT-' . uniqid();
$tenantId = 'cafe-brazil';
$name = 'Matcha Drinks';
$description = 'Hot and cold Matcha beverages';

// --- Insert the record using a prepared statement ---
// This is the correct, secure way to insert data.
$insertSql = "INSERT INTO product_categories (categoryId, tenantId, name, description, createdAt) 
              VALUES (?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($insertSql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// "ssss" indicates the type of the parameters: four strings.
$stmt->bind_param("ssss", $categoryId, $tenantId, $name, $description);

if ($stmt->execute()) {
    echo "Record successfully inserted into 'product_categories'.\n";
} else {
    echo "Error inserting record: " . $stmt->error . "\n";
}

// --- Close Connection ---
$stmt->close();
$conn->close();

?>
