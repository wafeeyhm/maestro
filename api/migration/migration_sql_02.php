<?php

// --- Database Configuration ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "maestro";

// --- Create Connection ---
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select the database
$conn->select_db($dbname);

// --- Check if the record already exists ---
$categoryId = 'CAT-12345';
$checkSql = "SELECT categoryId FROM product_categories WHERE categoryId = '$categoryId'";
$result = $conn->query($checkSql);

if ($result->num_rows > 0) {
    echo "Record with categoryId '{$categoryId}' already exists. No action taken.\n";
} else {

    // --- Insert the record if it does not exist ---
    $insertSql = "INSERT INTO product_categories (categoryId, tenantId, name, description, createdAt) 
    VALUES ('CAT-12345', 'cafe-brazil', 'Coffee Drinks', 'Hot and cold coffee beverages', NOW())";

    if ($conn->query($insertSql) === TRUE) {
        echo "Record successfully inserted into 'product_categories'.\n";
    } else {
        echo "Error inserting record: " . $conn->error . "\n";
    }
}

// --- Close Connection ---
$conn->close();

?>
