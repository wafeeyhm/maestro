<?php

require_once '../api/core/Database.php';

class ProductsController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createProduct() {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");

        $requestMethod = $_SERVER["REQUEST_METHOD"];
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed."]);
            return;
        }

        // Get tenantId from header
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

        $requiredFields = ['categoryId', 'name', 'description', 'baseCost', 'price', 'imageUrl', 'isActive'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Required field '{$field}' is missing."]);
                return;
            }
        }
        
        $productId = 'prod-' . uniqid();
        $categoryId = $data['categoryId'];
        $name = $data['name'];
        $description = $data['description'];
        $baseCost = (float) $data['baseCost'];
        $price = (float) $data['price'];
        $imageUrl = $data['imageUrl'];
        $isActive = (int) $data['isActive'];

        $sql = "INSERT INTO products (productId, tenantId, categoryId, name, description, baseCost, price, imageUrl, isActive) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssddsi", $productId, $tenantId, $categoryId, $name, $description, $baseCost, $price, $imageUrl, $isActive);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Product created successfully.", "productId" => $productId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error creating product: " . $stmt->error]);
        }
    }

    public function getAllProducts() {
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
        
        $sql = "SELECT * FROM products WHERE tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        http_response_code(200);
        echo json_encode($products);
    }
    
    public function getProduct($id) {
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

        $sql = "SELECT * FROM products WHERE productId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $id, $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            http_response_code(200);
            echo json_encode($result->fetch_assoc());
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Product not found."]);
        }
    }
    
    public function updateProduct($id) {
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
        if ($data === null) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload."]);
            return;
        }
        
        $setParams = [];
        $bindParams = [];
        $bindTypes = "";
        
        foreach ($data as $key => $value) {
            if ($key !== 'productId') {
                $setParams[] = "`{$key}` = ?";
                $bindParams[] = $value;
                $bindTypes .= (is_int($value) ? 'i' : (is_float($value) ? 'd' : 's'));
            }
        }

        if (empty($setParams)) {
            http_response_code(400);
            echo json_encode(["error" => "No fields to update."]);
            return;
        }
        
        $sql = "UPDATE products SET " . implode(', ', $setParams) . " WHERE productId = ? AND tenantId = ?";
        $bindTypes .= "ss"; // Add types for productId and tenantId
        $bindParams[] = $id;
        $bindParams[] = $tenantId;

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to prepare statement: " . $this->conn->error]);
            return;
        }
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$bindTypes], $this->refValues($bindParams)));

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Product updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error updating product: " . $stmt->error]);
        }
    }
    
    public function deleteProduct($id) {
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

        $sql = "DELETE FROM products WHERE productId = ? AND tenantId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $id, $tenantId);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Product deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error deleting product: " . $stmt->error]);
        }
    }
    
    private function refValues($arr){
        if (strnatcmp(phpversion(),'5.3') >= 0) {
            $refs = array();
            foreach($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }
}
?>
