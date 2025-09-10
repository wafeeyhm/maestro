<?php
// api/models/ProductModel.php

class ProductModel {
    // Database connection
    private $conn;
    private $table_name = "products";

    // Constructor to set up the database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to create a new product record
    public function create($data) {
        // SQL query to insert a new product record.
        // We're using a prepared statement to prevent SQL injection attacks.
        $query = "INSERT INTO " . $this->table_name . "
                  (productId, tenantId, categoryId, name, description, baseCost, price, imageUrl, isActive, createdAt) 
                  VALUES (:productId, :tenantId, :categoryId, :name, :description, :baseCost, :price, :imageUrl, :isActive, NOW())";
        
        // Prepare the query statement.
        $stmt = $this->conn->prepare($query);

        // Bind the data. PDO handles sanitation, so manual stripping is not needed.
        $productId = uniqid();
        $isActive = 1; // Assuming new products are active by default

        $stmt->bindParam(":productId", $productId);
        $stmt->bindParam(":tenantId", $data->tenantId); 
        $stmt->bindParam(":categoryId", $data->categoryId);
        $stmt->bindParam(":name", $data->name);
        $stmt->bindParam(":description", $data->description);
        $stmt->bindParam(":baseCost", $data->baseCost);
        $stmt->bindParam(":price", $data->price);
        $stmt->bindParam(":imageUrl", $data->imageUrl);
        $stmt->bindParam(":isActive", $isActive);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Method to read all products for a specific tenant
    public function read($tenantId) {
        $query = "SELECT p.*, c.name AS categoryName 
                  FROM " . $this->table_name . " p 
                  JOIN product_categories c ON p.categoryId = c.categoryId 
                  WHERE p.tenantId = :tenantId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tenantId", $tenantId);
        $stmt->execute();
        
        // Use PDO::FETCH_ASSOC to get an associative array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Method to read a single product by ID for a specific tenant
    public function readOne($productId, $tenantId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE productId = :productId AND tenantId = :tenantId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":productId", $productId);
        $stmt->bindParam(":tenantId", $tenantId);
        $stmt->execute();
        
        // Fetch a single row
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Method to update an existing product
    public function update($productId, $tenantId, $data) {
        // Build the dynamic SQL query
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "`{$key}` = :{$key}";
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setParts) . " WHERE productId = :productId AND tenantId = :tenantId";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters dynamically
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":{$key}", $value);
        }
        unset($value); // Unset the reference
        
        $stmt->bindParam(":productId", $productId);
        $stmt->bindParam(":tenantId", $tenantId);

        return $stmt->execute();
    }
    
    // Method to delete a product by ID
    public function delete($productId, $tenantId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE productId = :productId AND tenantId = :tenantId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":productId", $productId);
        $stmt->bindParam(":tenantId", $tenantId);
        
        return $stmt->execute();
    }
}
