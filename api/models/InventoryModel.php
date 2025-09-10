<?php
// api/models/InventoryModel.php

class InventoryModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($tenantId) {
        $sql = "SELECT * FROM inventory WHERE tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("s", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $inventory = [];
        while ($row = $result->fetch_assoc()) {
            $inventory[] = $row;
        }
        
        $stmt->close();
        return $inventory;
    }

    public function create($tenantId, $data) {
        $itemId = 'INV-' . uniqid();
        $productId = $data['productId'];
        $name = $data['name'];
        $quantity = (int) $data['quantity'];
        $unit = $data['unit'];
        
        $sql = "INSERT INTO inventory (itemId, tenantId, productId, name, quantity, unit, lastUpdated) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("sssiss", $itemId, $tenantId, $productId, $name, $quantity, $unit);

        if ($stmt->execute()) {
            $stmt->close();
            return $itemId;
        } else {
            error_log("Error creating inventory item: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    public function update($itemId, $tenantId, $quantity) {
        $sql = "UPDATE inventory SET quantity = ?, lastUpdated = NOW() WHERE itemId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("iss", $quantity, $itemId, $tenantId);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log("Error updating inventory item: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    public function delete($itemId, $tenantId) {
        $sql = "DELETE FROM inventory WHERE itemId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("ss", $itemId, $tenantId);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log("Error deleting inventory item: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
}
