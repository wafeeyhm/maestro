<?php
// api/models/LogModel.php

class LogModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Records an action in the audit_logs table.
     *
     * @param string $tenantId The tenant ID associated with the action.
     * @param string $userId The user ID who performed the action.
     * @param string $action A brief description of the action (e.g., 'user_registered').
     * @param array $details An associative array with additional details (optional).
     */
    public function logAction($tenantId, $userId, $action, $details = []) {
        // Generate a unique ID for the log entry
        $logId = 'LOG-' . uniqid();
        
        $stmt = $this->db->prepare("
            INSERT INTO audit_logs (logId, tenantId, userId, action, details, timestamp) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $detailsJson = json_encode($details);
        $stmt->bind_param("sssss", $logId, $tenantId, $userId, $action, $detailsJson);

        if (!$stmt->execute()) {
            error_log("Failed to log action: " . $stmt->error);
        }

        $stmt->close();
    }
}
