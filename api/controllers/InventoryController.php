<?php
// api/controllers/InventoryController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/InventoryModel.php';

class InventoryController {
    private $inventoryModel;
    private $tenantId;

    public function __construct($db) {
        $this->inventoryModel = new InventoryModel($db);
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

    public function getAllInventory() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $inventory = $this->inventoryModel->getAll($tenantId);
        
        http_response_code(200);
        echo json_encode($inventory);
    }
    
    public function createInventoryItem() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload."]);
            return;
        }
        
        $requiredFields = ['productId', 'name', 'quantity', 'unit'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Required field '{$field}' is missing."]);
                return;
            }
        }
        
        $itemId = $this->inventoryModel->create($tenantId, $data);

        if ($itemId) {
            http_response_code(201);
            echo json_encode(["message" => "Inventory item created successfully.", "itemId" => $itemId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error creating inventory item."]);
        }
    }
    
    public function updateInventoryItem($id) {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null || !isset($data['quantity'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload or missing quantity."]);
            return;
        }
        
        $quantity = (int) $data['quantity'];

        if ($this->inventoryModel->update($id, $tenantId, $quantity)) {
            http_response_code(200);
            echo json_encode(["message" => "Inventory item updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error updating inventory item."]);
        }
    }
    
    public function deleteInventoryItem($id) {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        if ($this->inventoryModel->delete($id, $tenantId)) {
            http_response_code(200);
            echo json_encode(["message" => "Inventory item deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error deleting inventory item."]);
        }
    }
}
