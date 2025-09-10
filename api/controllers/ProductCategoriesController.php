<?php
// api/controllers/ProductCategoriesController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/ProductCategoryModel.php';

class ProductCategoriesController {
    private $productCategoryModel;
    private $tenantId;

    public function __construct($db) {
        $this->productCategoryModel = new ProductCategoryModel($db);
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

    public function createCategory() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $data = json_decode(file_get_contents('php://input'), true);

        // Basic validation
        if (!isset($data['name']) || !isset($data['description'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Category name and description are required.']);
            return;
        }

        if ($this->productCategoryModel->create($tenantId, $data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Product category created successfully!']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create product category.']);
        }
    }
    
    public function getCategories() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;
        
        $categories = $this->productCategoryModel->getAll($tenantId);

        http_response_code(200);
        echo json_encode($categories);
    }
}
