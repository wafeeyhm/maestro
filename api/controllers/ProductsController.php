<?php

// Include necessary models.
require_once '..\models\ProductModel.php';
require_once '..\models\LogModel.php';

class ProductsController {
    private $db;
    private $productModel;
    private $logModel;

    public function __construct($db, $logModel) {
        $this->db = $db;
        $this->productModel = new ProductModel($this->db);
        $this->logModel = $logModel;
    }

    public function createProduct() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (
            !empty($data->name) &&
            !empty($data->price) &&
            !empty($data->category_id)
        ) {
            if ($this->productModel->create($data)) {
                $this->logModel->logEvent('product_created', ['product_name' => $data->name]);
                http_response_code(201);
                echo json_encode(['message' => 'Product created successfully.']);
            } else {
                http_response_code(503);
                echo json_encode(['error' => 'Unable to create product.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Incomplete data.']);
        }
    }

    // Retrieves all products for a specific tenant
    public function getAllProducts() {
        // NOTE: This placeholder tenantId will be replaced with a real ID from the authenticated user.
        $tenantId = 'your_tenant_id_here'; 
        $products = $this->productModel->read($tenantId);
        
        if ($products) {
            http_response_code(200);
            echo json_encode($products);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'No products found.']);
        }
    }
    
    // Retrieves a single product by its ID for a specific tenant
    public function getProduct($id) {
        // NOTE: This placeholder tenantId will be replaced with a real ID from the authenticated user.
        $tenantId = 'your_tenant_id_here'; 
        $product = $this->productModel->readOne($id, $tenantId);
        
        if ($product) {
            http_response_code(200);
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Product not found.']);
        }
    }
    
    // Updates an existing product for a specific tenant
    public function updateProduct($id) {
        $data = json_decode(file_get_contents("php://input"));
        // NOTE: This placeholder tenantId will be replaced with a real ID from the authenticated user.
        $tenantId = 'your_tenant_id_here';
        
        if ($this->productModel->update($id, $tenantId, $data)) {
            $this->logModel->logEvent('product_updated', ['product_id' => $id]);
            http_response_code(200);
            echo json_encode(['message' => 'Product updated successfully.']);
        } else {
            http_response_code(503);
            echo json_encode(['error' => 'Unable to update product.']);
        }
    }

    // Deletes a product by its ID for a specific tenant
    public function deleteProduct($id) {
        // NOTE: This placeholder tenantId will be replaced with a real ID from the authenticated user.
        $tenantId = 'your_tenant_id_here';
        if ($this->productModel->delete($id, $tenantId)) {
            $this->logModel->logEvent('product_deleted', ['product_id' => $id]);
            http_response_code(200);
            echo json_encode(['message' => 'Product deleted successfully.']);
        } else {
            http_response_code(503);
            echo json_encode(['error' => 'Unable to delete product.']);
        }
    }
}
