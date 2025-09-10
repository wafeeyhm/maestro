<?php
// api/controllers/ProductsController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/ProductModel.php';

class ProductsController {
    private $productModel;
    private $tenantId;

    public function __construct($db) {
        $this->productModel = new ProductModel($db);
        $headers = getallheaders();
        $this->tenantId = $headers['X-Tenant-Id'] ?? null;
    }

    private function getTenantId() {
        if (empty($this->tenantId)) {
            http_response_code(400);
            echo json_encode(["error" => "Tenant ID header is missing."]);
            return null;
        }
        return $this->tenantId;
    }

    public function createProduct() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload."]);
            return;
        }
        
        $requiredFields = ['categoryId', 'name', 'description', 'price'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Required field '{$field}' is missing."]);
                return;
            }
        }
        
        $productId = $this->productModel->create($tenantId, $data);
        
        if ($productId) {
            http_response_code(201);
            echo json_encode(["message" => "Product created successfully.", "productId" => $productId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error creating product."]);
        }
    }

    public function getAllProducts() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $products = $this->productModel->getAll($tenantId);
        
        http_response_code(200);
        echo json_encode($products);
    }
    
    public function getProduct($id) {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $product = $this->productModel->getOne($id, $tenantId);
        
        if ($product) {
            http_response_code(200);
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Product not found."]);
        }
    }
    
    public function updateProduct($id) {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload."]);
            return;
        }

        if ($this->productModel->update($id, $tenantId, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Product updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error updating product."]);
        }
    }
    
    public function deleteProduct($id) {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        if ($this->productModel->delete($id, $tenantId)) {
            http_response_code(200);
            echo json_encode(["message" => "Product deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error deleting product."]);
        }
    }
}
