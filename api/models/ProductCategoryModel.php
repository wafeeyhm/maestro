<?php
// api/models/ProductCategoryModel.php

class ProductCategoryModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($tenantId, $data) {
        $categoryId = 'cat-' . uniqid();
        $name = $data['name'];
        $description = $data['description'];

        $stmt = $this->conn->prepare("INSERT INTO product_categories (categoryId, tenantId, name, description, createdAt) VALUES (?, ?, ?, ?, NOW())");
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ssss", $categoryId, $tenantId, $name, $description);

        if ($stmt->execute()) {
            $stmt->close();
            return $categoryId;
        } else {
            error_log("Error creating product category: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    public function getAll($tenantId) {
        $stmt = $this->conn->prepare("SELECT * FROM product_categories WHERE tenantId = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("s", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        $stmt->close();
        return $categories;
    }
}
