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
$tenantId = 'cafe-brazil';
$productId = 'prod-68be673b8cddb'; // IMPORTANT: This productId MUST exist in your `products` table
$itemId = 'INV-' . uniqid();
$name = 'Matcha powder';
$quantity = 150;
$unit = 'kg';

// --- Check if the record already exists ---
// NOTE: Since uniqid() is designed to be unique, this check is often not needed,
// but it's kept here to show the pattern for checking existing records.
$checkSql = "SELECT itemId FROM inventory WHERE itemId = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("s", $itemId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Record with itemId '{$itemId}' already exists. No action taken.\n";
} else {
    // --- Insert the record if it does not exist ---
    $insertSql = "INSERT INTO inventory (itemId, tenantId, productId, name, quantity, unit, lastUpdated) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($insertSql);

    // The corrected type string is "ssssis"
    // s - itemId (string)
    // s - tenantId (string)
    // s - productId (string)
    // s - name (string)
    // i - quantity (integer)
    // s - unit (string)
    $stmt->bind_param("ssssis", $itemId, $tenantId, $productId, $name, $quantity, $unit);

    if ($stmt->execute()) {
        echo "Record successfully inserted into 'inventory'.\n";
    } else {
        echo "Error inserting record: " . $stmt->error . "\n";
    }
}

// --- Close Connection ---
$stmt->close();
$conn->close();

?>
