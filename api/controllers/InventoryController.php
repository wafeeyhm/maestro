<?php

require_once '../api/core/Database.php';

class InventoryController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllInventory() {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");

        $requestMethod = $_SERVER["REQUEST_METHOD"];
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed."]);
            return;
        }
        
        $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? '';
        if (empty($tenantId)) {
            http_response_code(400);
            echo json_encode(["error" => "Tenant ID header is missing."]);
            return;
        }
        
        $sql = "SELECT * FROM inventory WHERE tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $inventory = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $inventory[] = $row;
            }
        }
        
        http_response_code(200);
        echo json_encode($inventory);
    }
    
    public function createInventoryItem() {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");
        
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed."]);
            return;
        }
        
        $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? '';
        if (empty($tenantId)) {
            http_response_code(400);
            echo json_encode(["error" => "Tenant ID header is missing."]);
            return;
        }

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
        
        $itemId = 'INV-' . uniqid();
        $productId = $data['productId'];
        $name = $data['name'];
        $quantity = (int) $data['quantity'];
        $unit = $data['unit'];
        
        $sql = "INSERT INTO inventory (itemId, tenantId, productId, name, quantity, unit, lastUpdated) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssiss", $itemId, $tenantId, $productId, $name, $quantity, $unit);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Inventory item created successfully.", "itemId" => $itemId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error creating inventory item: " . $stmt->error]);
        }
    }
    
    public function updateInventoryItem($id) {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");

        $requestMethod = $_SERVER["REQUEST_METHOD"];
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed."]);
            return;
        }

        $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? '';
        if (empty($tenantId)) {
            http_response_code(400);
            echo json_encode(["error" => "Tenant ID header is missing."]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null || empty($data['quantity'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload or missing quantity."]);
            return;
        }
        
        $quantity = (int) $data['quantity'];

        $sql = "UPDATE inventory SET quantity = ?, lastUpdated = NOW() WHERE itemId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $quantity, $id, $tenantId);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Inventory item updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error updating inventory item: " . $stmt->error]);
        }
    }
    
    public function deleteInventoryItem($id) {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");

        $requestMethod = $_SERVER["REQUEST_METHOD"];
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed."]);
            return;
        }

        $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? '';
        if (empty($tenantId)) {
            http_response_code(400);
            echo json_encode(["error" => "Tenant ID header is missing."]);
            return;
        }

        $sql = "DELETE FROM inventory WHERE itemId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $id, $tenantId);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Inventory item deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error deleting inventory item: " . $stmt->error]);
        }
    }
}
?>
