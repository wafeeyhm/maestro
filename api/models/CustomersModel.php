<?php
// api/models/CustomersModel.php

class CustomersModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($tenantId, $data) {
        $customerId = 'CUST-' . uniqid();
        $name = $data['name'];
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        $notes = $data['notes'] ?? null;

        $stmt = $this->conn->prepare("INSERT INTO customers (customerId, tenantId, name, email, phone, notes, createdAt) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $customerId, $tenantId, $name, $email, $phone, $notes);

        if ($stmt->execute()) {
            $stmt->close();
            return $customerId;
        } else {
            error_log("Error creating customer: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    public function getAll($tenantId) {
        $stmt = $this->conn->prepare("SELECT * FROM customers WHERE tenantId = ?");
        $stmt->bind_param("s", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        
        $stmt->close();
        return $customers;
    }

    public function getById($customerId, $tenantId) {
        $stmt = $this->conn->prepare("SELECT * FROM customers WHERE customerId = ? AND tenantId = ?");
        $stmt->bind_param("ss", $customerId, $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        $stmt->close();
        return $customer;
    }

    public function update($customerId, $tenantId, $data) {
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        $notes = $data['notes'] ?? null;
    
        $sql = "UPDATE customers SET name = ?, email = ?, phone = ?, notes = ? WHERE customerId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $phone, $notes, $customerId, $tenantId);
    
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log("Error updating customer: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    public function delete($customerId, $tenantId) {
        $stmt = $this->conn->prepare("DELETE FROM customers WHERE customerId = ? AND tenantId = ?");
        $stmt->bind_param("ss", $customerId, $tenantId);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log("Error deleting customer: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
}
