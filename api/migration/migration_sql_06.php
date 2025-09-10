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
$customerId = 'CUST-' . uniqid();
$tenantId = 'cafe-brazil';
$name = 'Walk In';
$email = 'walkin@cafebrazil.com';
$phone = '123-456-7890';
$notes = 'Prefers oat milk in her coffee.';

// --- Insert the record using a prepared statement ---
$insertSql = "INSERT INTO customers (customerId, tenantId, name, email, phone, notes, createdAt) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($insertSql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// "ssssss" indicates the type of the parameters: six strings.
$stmt->bind_param("ssssss", $customerId, $tenantId, $name, $email, $phone, $notes);

if ($stmt->execute()) {
    echo "Record successfully inserted into 'customers'.\n";
} else {
    echo "Error inserting record: " . $stmt->error . "\n";
}

// --- Close Connection ---
$stmt->close();
$conn->close();

?>
