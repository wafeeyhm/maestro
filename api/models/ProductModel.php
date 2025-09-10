<?php
// api/models/ProductModel.php

class ProductModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($tenantId, $data) {
        $sql = "INSERT INTO products (productId, tenantId, categoryId, name, description, baseCost, price, imageUrl, isActive) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return false;
        }

        $productId = 'prod-' . uniqid();
        $isActive = (int) $data['isActive'];

        $stmt->bind_param(
            "sssssddsi", 
            $productId,
            $tenantId, 
            $data['categoryId'], 
            $data['name'], 
            $data['description'], 
            $data['baseCost'], 
            $data['price'], 
            $data['imageUrl'], 
            $isActive
        );

        if ($stmt->execute()) {
            return $productId;
        } else {
            error_log("Error creating product: " . $stmt->error);
            return false;
        }
    }

    public function getAll($tenantId) {
        $sql = "SELECT p.*, c.name AS categoryName FROM products p JOIN product_categories c ON p.categoryId = c.categoryId WHERE p.tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("s", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }
    
    public function getOne($productId, $tenantId) {
        $sql = "SELECT * FROM products WHERE productId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return null;
        }

        $stmt->bind_param("ss", $productId, $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    public function update($productId, $tenantId, $data) {
        // Build the dynamic SQL query
        $setParams = [];
        $bindParams = [];
        $bindTypes = "";
        
        foreach ($data as $key => $value) {
            $setParams[] = "`{$key}` = ?";
            $bindParams[] = $value;
            if (is_int($value)) {
                $bindTypes .= 'i';
            } elseif (is_float($value)) {
                $bindTypes .= 'd';
            } else {
                $bindTypes .= 's';
            }
        }
        
        $sql = "UPDATE products SET " . implode(', ', $setParams) . " WHERE productId = ? AND tenantId = ?";
        $bindTypes .= "ss"; // Add types for productId and tenantId
        $bindParams[] = $productId;
        $bindParams[] = $tenantId;

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return false;
        }

        // We use call_user_func_array to handle the dynamic parameters
        $refs = [];
        foreach ($bindParams as $key => $value) {
            $refs[$key] = &$bindParams[$key];
        }
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$bindTypes], $refs));

        return $stmt->execute();
    }
    
    public function delete($productId, $tenantId) {
        $sql = "DELETE FROM products WHERE productId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("ss", $productId, $tenantId);
        return $stmt->execute();
    }
}
