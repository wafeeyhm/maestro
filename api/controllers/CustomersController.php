<?php
// api/controllers/CustomersController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/CustomersModel.php';

class CustomersController {
    private $customersModel;
    private $tenantId;

    public function __construct($db) {
        $this->customersModel = new CustomersModel($db);
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

    public function getAllCustomers() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $customers = $this->customersModel->getAll($tenantId);
        
        http_response_code(200);
        echo json_encode($customers);
    }

    public function getCustomer($id) {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $customer = $this->customersModel->getById($id, $tenantId);
        if ($customer) {
            http_response_code(200);
            echo json_encode($customer);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Customer not found."]);
        }
    }

    public function createCustomer() {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null || !isset($data['name'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload or missing customer name."]);
            return;
        }
        
        $customerId = $this->customersModel->create($tenantId, $data);
        if ($customerId) {
            http_response_code(201);
            echo json_encode(["message" => "Customer created successfully.", "customerId" => $customerId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error creating customer."]);
        }
    }
    
    public function updateCustomer($id) {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload."]);
            return;
        }
        
        if ($this->customersModel->update($id, $tenantId, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Customer updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error updating customer."]);
        }
    }

    public function deleteCustomer($id) {
        header("Content-Type: application/json");
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        if ($this->customersModel->delete($id, $tenantId)) {
            http_response_code(200);
            echo json_encode(["message" => "Customer deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error deleting customer."]);
        }
    }
}
