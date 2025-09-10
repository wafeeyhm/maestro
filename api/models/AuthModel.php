<?php
// api/models/AuthModel.php

class AuthModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Checks if a tenant with the given ID exists.
     * @param string $tenantId The ID of the tenant to check.
     * @return bool True if the tenant exists, false otherwise.
     */
    public function tenantExists($tenantId) {
        $stmt = $this->db->prepare("SELECT tenantId FROM tenants WHERE tenantId = ?");
        $stmt->bind_param("s", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /**
     * Finds a user by their email address.
     * @param string $email The email of the user to find.
     * @return array|null The user's data as an associative array, or null if not found.
     */
    public function findUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT userId, tenantId, passwordHash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    /**
     * Registers a new user.
     * @param array $userData An array containing user details.
     * @return string|null The newly created userId on success, or null on failure.
     */
    public function registerUser($userData) {
        $userId = uniqid();
        $stmt = $this->db->prepare("INSERT INTO users (userId, tenantId, email, passwordHash, firstName, lastName, role, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        
        // Hash the password for security
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $stmt->bind_param("sssssss",
            $userId,
            $userData['tenantId'],
            $userData['email'],
            $passwordHash,
            $userData['firstName'],
            $userData['lastName'],
            $userData['role']
        );

        if ($stmt->execute()) {
            $stmt->close();
            return $userId;
        } else {
            $stmt->close();
            return null;
        }
    }
}
