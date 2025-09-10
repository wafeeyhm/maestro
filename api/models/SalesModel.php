<?php
// api/models/SalesModel.php

class SalesModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createSale($tenantId, $data) {
        // Start a database transaction to ensure atomicity
        $this->conn->begin_transaction();

        try {
            // 1. Create a new sale record
            $saleId = 'SALE-' . uniqid();
            $userId = $data['userId'];
            $customerId = $data['customerId'] ?? null;
            $paymentMethod = $data['paymentMethod'];
            $totalAmount = $data['totalAmount'];

            $saleSql = "INSERT INTO sales (saleId, tenantId, userId, customerId, paymentMethod, totalAmount, createdAt) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $saleStmt = $this->conn->prepare($saleSql);
            if (!$saleStmt) {
                throw new Exception("Failed to prepare sales statement: " . $this->conn->error);
            }
            $saleStmt->bind_param("sssssd", $saleId, $tenantId, $userId, $customerId, $paymentMethod, $totalAmount);
            if (!$saleStmt->execute()) {
                throw new Exception("Error creating sale: " . $saleStmt->error);
            }
            $saleStmt->close();

            // 2. Add each item to the sales_items table and update inventory
            foreach ($data['items'] as $item) {
                $itemId = 'SITEM-' . uniqid();
                $productId = $item['productId'];
                $quantity = (int) $item['quantity'];
                $price = (float) $item['price'];

                // Insert into sales_items
                $itemSql = "INSERT INTO sales_items (itemId, saleId, productId, quantity, price, createdAt) VALUES (?, ?, ?, ?, ?, NOW())";
                $itemStmt = $this->conn->prepare($itemSql);
                if (!$itemStmt) {
                    throw new Exception("Failed to prepare sales_items statement: " . $this->conn->error);
                }
                $itemStmt->bind_param("sssid", $itemId, $saleId, $productId, $quantity, $price);
                if (!$itemStmt->execute()) {
                    throw new Exception("Error creating sales item: " . $itemStmt->error);
                }
                $itemStmt->close();

                // Update inventory by reducing the stock
                $inventorySql = "UPDATE inventory SET quantity = quantity - ?, lastUpdated = NOW() WHERE productId = ? AND tenantId = ?";
                $inventoryStmt = $this->conn->prepare($inventorySql);
                if (!$inventoryStmt) {
                    throw new Exception("Failed to prepare inventory update statement: " . $this->conn->error);
                }
                $inventoryStmt->bind_param("iss", $quantity, $productId, $tenantId);
                if (!$inventoryStmt->execute()) {
                    throw new Exception("Error updating inventory: " . $inventoryStmt->error);
                }
                $inventoryStmt->close();
            }

            // 3. Commit the transaction if all queries were successful
            $this->conn->commit();
            return $saleId;

        } catch (Exception $e) {
            // Rollback the transaction on any failure
            $this->conn->rollback();
            error_log("Transaction failed: " . $e->getMessage());
            return false;
        }
    }

    public function getAllSales($tenantId) {
        $sql = "SELECT * FROM sales WHERE tenantId = ? ORDER BY createdAt DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sales = [];
        while ($row = $result->fetch_assoc()) {
            $sales[] = $row;
        }
        
        $stmt->close();
        return $sales;
    }

    public function getSaleAndItems($saleId, $tenantId) {
        // Fetch the main sale record
        $saleSql = "SELECT * FROM sales WHERE saleId = ? AND tenantId = ?";
        $saleStmt = $this->conn->prepare($saleSql);
        $saleStmt->bind_param("ss", $saleId, $tenantId);
        $saleStmt->execute();
        $saleResult = $saleStmt->get_result();
        $sale = $saleResult->fetch_assoc();
        $saleStmt->close();

        if (!$sale) {
            return null; // Sale not found
        }

        // Fetch the items for the sale
        $itemsSql = "SELECT * FROM sales_items WHERE saleId = ?";
        $itemsStmt = $this->conn->prepare($itemsSql);
        $itemsStmt->bind_param("s", $saleId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        $items = [];
        while ($row = $itemsResult->fetch_assoc()) {
            $items[] = $row;
        }
        $itemsStmt->close();

        $sale['items'] = $items;
        return $sale;
    }
}
