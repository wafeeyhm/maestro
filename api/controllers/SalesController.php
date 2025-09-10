<?php
// api/controllers/SalesController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/SalesModel.php';
require_once __DIR__ . '/../models/InventoryModel.php';

class SalesController {
    private $salesModel;
    private $tenantId;

    public function __construct($db) {
        $this->salesModel = new SalesModel($db);
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

    public function getAllSales() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $sales = $this->salesModel->getAllSales($tenantId);
        
        http_response_code(200);
        echo json_encode($sales);
    }

    public function getSale($id) {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $sale = $this->salesModel->getSaleAndItems($id, $tenantId);
        
        if ($sale) {
            http_response_code(200);
            echo json_encode($sale);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Sale not found."]);
        }
    }

    public function createSale() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload."]);
            return;
        }
        
        $requiredFields = ['userId', 'paymentMethod', 'totalAmount', 'items'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Required field '{$field}' is missing."]);
                return;
            }
        }
        
        $saleId = $this->salesModel->createSale($tenantId, $data);

        if ($saleId) {
            http_response_code(201);
            echo json_encode(["message" => "Sale created successfully.", "saleId" => $saleId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error creating sale."]);
        }
    }
}
