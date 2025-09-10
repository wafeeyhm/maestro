<?php
// api/controllers/SalesController.php

class SalesController {
    private $conn;
    private $tenantId;

    public function __construct($db) {
        $this->conn = $db;
        $headers = getallheaders();
        $this->tenantId = isset($headers['X-Tenant-Id']) ? $headers['X-Tenant-Id'] : die(json_encode(["error" => "Tenant ID not provided."]));
    }

    // Handles POST request to create a new sale
    public function createSale() {
        $data = json_decode(file_get_contents("php://input"));

        // Basic validation
        if (!isset($data->userId) || !isset($data->paymentMethod) || !isset($data->items) || !is_array($data->items)) {
            http_response_code(400);
            echo json_encode(["error" => "Incomplete sale data."]);
            return;
        }

        // Start database transaction
        $this->conn->begin_transaction();

        try {
            // Step 1: Insert into 'orders' table
            $orderId = 'order-' . bin2hex(random_bytes(6));
            $totalAmount = 0;

            $query = "INSERT INTO orders (orderId, tenantId, userId, customerId, status, totalAmount, paymentMethod, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($query);

            $status = "completed";
            $customerId = $data->customerId ?? null;
            $stmt->bind_param("ssssdss", $orderId, $this->tenantId, $data->userId, $customerId, $status, $totalAmount, $data->paymentMethod);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order.");
            }

            // Step 2: Loop through items, insert into 'order_items', and update 'inventory'
            $orderItemsQuery = "INSERT INTO order_items (orderItemId, orderId, productId, quantity, priceAtSale) VALUES (?, ?, ?, ?, ?)";
            $updateInventoryQuery = "UPDATE inventory SET quantity = quantity - ?, lastUpdated = NOW() WHERE productId = ? AND tenantId = ?";

            $orderItemsStmt = $this->conn->prepare($orderItemsQuery);
            $updateInventoryStmt = $this->conn->prepare($updateInventoryQuery);
            
            foreach ($data->items as $item) {
                // Insert into 'order_items'
                $orderItemId = 'order_item-' . bin2hex(random_bytes(6));
                $priceAtSale = $item->price; // Use the price from the payload
                $orderItemsStmt->bind_param("sssid", $orderItemId, $orderId, $item->productId, $item->quantity, $priceAtSale);
                
                if (!$orderItemsStmt->execute()) {
                    throw new Exception("Failed to insert order item.");
                }

                // Update 'inventory'
                $updateInventoryStmt->bind_param("iss", $item->quantity, $item->productId, $this->tenantId);
                if (!$updateInventoryStmt->execute() || $this->conn->affected_rows === 0) {
                     // Check if item exists and if the quantity was updated
                     throw new Exception("Failed to update inventory for product: " . $item->productId);
                }

                // Update total amount
                $totalAmount += $item->price * $item->quantity;
            }

            // Step 3: Update the 'totalAmount' in the 'orders' table
            $updateOrderQuery = "UPDATE orders SET totalAmount = ? WHERE orderId = ?";
            $updateOrderStmt = $this->conn->prepare($updateOrderQuery);
            $updateOrderStmt->bind_param("ds", $totalAmount, $orderId);

            if (!$updateOrderStmt->execute()) {
                throw new Exception("Failed to update order total amount.");
            }

            // Commit the transaction if all queries were successful
            $this->conn->commit();
            http_response_code(201);
            echo json_encode(["message" => "Sale created successfully.", "orderId" => $orderId, "totalAmount" => $totalAmount]);

        } catch (Exception $e) {
            // Rollback the transaction on any failure
            $this->conn->rollback();
            http_response_code(500);
            echo json_encode(["error" => "Transaction failed: " . $e->getMessage()]);
        }
    }

    // Handles GET request to retrieve a single sale by ID
    public function getSale($orderId) {
        // Query to get the main order details
        $orderQuery = "
            SELECT
                o.orderId,
                o.totalAmount,
                o.createdAt,
                o.status,
                u.firstName AS userFirstName,
                u.lastName AS userLastName,
                c.name AS customerName
            FROM orders o
            JOIN users u ON o.userId = u.userId
            LEFT JOIN customers c ON o.customerId = c.customerId
            WHERE o.orderId = ? AND o.tenantId = ?";
        
        $orderStmt = $this->conn->prepare($orderQuery);
        $orderStmt->bind_param("ss", $orderId, $this->tenantId);
        $orderStmt->execute();
        $orderResult = $orderStmt->get_result();
        $order = $orderResult->fetch_assoc();

        if (!$order) {
            http_response_code(404);
            echo json_encode(["message" => "Sale record not found."]);
            return;
        }

        // Query to get items for the specific order
        $itemsQuery = "
            SELECT
                oi.quantity,
                oi.priceAtSale,
                p.name AS productName,
                p.productId
            FROM order_items oi
            JOIN products p ON oi.productId = p.productId
            WHERE oi.orderId = ?";
        
        $itemsStmt = $this->conn->prepare($itemsQuery);
        $itemsStmt->bind_param("s", $orderId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();

        $order['items'] = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $order['items'][] = $item;
        }
        
        echo json_encode($order);
    }

    // Handles GET request to retrieve all sales for the tenant, with optional date filtering
    public function getAllSales() {
        $sales = [];
        $params = [];
        $types = "";

        $startDate = $_GET['startDate'] ?? null;
        $endDate = $_GET['endDate'] ?? null;
        
        // --- Fix for the off-by-one date bug ---
        if ($endDate) {
            try {
                $dt = new DateTime($endDate);
                $dt->modify('+1 day');
                $endDate = $dt->format('Y-m-d');
            } catch (Exception $e) {
                // If the date format is invalid, just use the original value
            }
        }
        // --- End of fix ---

        // Build the base query and WHERE clause
        $ordersQuery = "
            SELECT
                o.orderId,
                o.totalAmount,
                o.createdAt,
                o.status,
                u.firstName AS userFirstName,
                u.lastName AS userLastName,
                c.name AS customerName
            FROM orders o
            JOIN users u ON o.userId = u.userId
            LEFT JOIN customers c ON o.customerId = c.customerId
            WHERE o.tenantId = ?";
        
        $params[] = $this->tenantId;
        $types .= "s";

        // Add date filtering to the query if parameters are provided
        if ($startDate) {
            $ordersQuery .= " AND o.createdAt >= ?";
            $params[] = $startDate;
            $types .= "s";
        }
        if ($endDate) {
            $ordersQuery .= " AND o.createdAt <= ?";
            $params[] = $endDate;
            $types .= "s";
        }

        $ordersQuery .= " ORDER BY o.createdAt DESC";

        $ordersStmt = $this->conn->prepare($ordersQuery);
        
        // Use call_user_func_array to bind parameters dynamically
        $bindParams = array_merge([$types], $params);
        call_user_func_array([$ordersStmt, 'bind_param'], $this->refValues($bindParams));

        $ordersStmt->execute();
        $ordersResult = $ordersStmt->get_result();

        // Query to get items for a specific order
        $itemsQuery = "
            SELECT
                oi.quantity,
                oi.priceAtSale,
                p.name AS productName,
                p.productId
            FROM order_items oi
            JOIN products p ON oi.productId = p.productId
            WHERE oi.orderId = ?";
        
        $itemsStmt = $this->conn->prepare($itemsQuery);

        while ($order = $ordersResult->fetch_assoc()) {
            $order['items'] = [];
            
            $itemsStmt->bind_param("s", $order['orderId']);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();

            while ($item = $itemsResult->fetch_assoc()) {
                $order['items'][] = $item;
            }
            
            $sales[] = $order;
        }

        if (count($sales) > 0) {
            echo json_encode($sales);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No sales records found."]);
        }
    }
    
    // Helper function to pass by reference for dynamic binding
    private function refValues($arr){
        if (strnatcmp(phpversion(),'5.3') >= 0) { //Reference is required for PHP 5.3+
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }
}
?>
