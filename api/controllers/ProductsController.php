<?php
// api/controllers/ProductsController.php

require_once __DIR__ . '/../core/Database.php';

class ProductsController {
    private $conn;
    private $tenantId;

    public function __construct($db) {
        $this->conn = $db;
        $headers = getallheaders();
        $this->tenantId = isset($headers['X-Tenant-Id']) ? $headers['X-Tenant-Id'] : die(json_encode(["error" => "Tenant ID not provided."]));
    }

    // Handles GET request to retrieve all products for the tenant
    public function getAllProducts() {
        $query = "SELECT productId, name, description, price, imageUrl, createdAt FROM products WHERE tenantId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->tenantId);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        echo json_encode($products);
    }

    // Handles GET request to retrieve a single product by ID
    public function getProduct($id) {
        $query = "SELECT productId, name, description, price, imageUrl, createdAt FROM products WHERE productId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $id, $this->tenantId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Product not found."]);
        }
    }

    // Handles POST request to create a new product
    public function createProduct() {
        $data = json_decode(file_get_contents("php://input"));

        // A simple check to ensure all required data is present
        if (!isset($data->categoryId) || !isset($data->name) || !isset($data->baseCost) || !isset($data->price)) {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete product data."]);
            return;
        }

        // Generate unique product ID
        $productId = 'prod-' . bin2hex(random_bytes(6));

        $query = "INSERT INTO products (productId, tenantId, categoryId, name, description, baseCost, price, imageUrl, isActive, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);

        // Bind parameters. 'sssssddsi' for 5 strings, 2 doubles, 1 string, 1 integer
        $stmt->bind_param(
            "sssssddsi", 
            $productId, 
            $this->tenantId, 
            $data->categoryId, 
            $data->name, 
            $data->description, 
            $data->baseCost,
            $data->price, 
            $data->imageUrl, 
            $data->isActive
        );
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Product created successfully.", "productId" => $productId]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to create product.", "error" => $stmt->error]);
        }
    }

    // Handles PUT request to update an existing product
    public function updateProduct($id) {
        $data = json_decode(file_get_contents("php://input"));
        $query = "UPDATE products SET categoryId = ?, name = ?, description = ?, baseCost = ?, price = ?, imageUrl = ?, isActive = ?, updatedAt = NOW() WHERE productId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($query);

        // Bind parameters. 'ssdsdisss' for 3 strings, 1 double, 1 double, 1 string, 1 integer, 2 strings
        $stmt->bind_param(
            "sssddisss", 
            $data->categoryId, 
            $data->name, 
            $data->description,
            $data->baseCost,
            $data->price, 
            $data->imageUrl, 
            $data->isActive, 
            $id,
            $this->tenantId
        );
        
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Product updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update product.", "error" => $stmt->error]);
        }
    }

    // Handles DELETE request to delete a product by ID
    public function deleteProduct($id) {
        $query = "DELETE FROM products WHERE productId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $id, $this->tenantId);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Product deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete product.", "error" => $stmt->error]);
        }
    }
}
