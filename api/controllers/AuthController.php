<?php
// api/controllers/AuthController.php

require_once __DIR__ . '/../core/Database.php';

class AuthController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Basic validation
        if (!isset($data['tenantId']) || !isset($data['email']) || !isset($data['password']) || !isset($data['firstName']) || !isset($data['lastName']) || !isset($data['role'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All required fields are not provided.']);
            return;
        }

        $tenantId = $data['tenantId'];
        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the password
        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $role = $data['role'];

        // Check if the provided tenantId exists
        $stmt = $this->db->prepare("SELECT tenantId FROM tenants WHERE tenantId = ?");
        $stmt->bind_param("s", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Tenant not found. You can only register users to an existing cafe.']);
            return;
        }
        $stmt->close();

        // Check if user already exists with the same email
        $stmt = $this->db->prepare("SELECT userId FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already registered.']);
            return;
        }
        $stmt->close();

        // Insert new user into the database
        $userId = uniqid(); // Generate a simple unique ID for now
        $stmt = $this->db->prepare("INSERT INTO users (userId, tenantId, email, passwordHash, firstName, lastName, role, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssss", $userId, $tenantId, $email, $password, $firstName, $lastName, $role);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['message' => 'User registered successfully!', 'userId' => $userId, 'tenantId' => $tenantId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to register user.']);
        }

        $stmt->close();
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Basic validation
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required.']);
            return;
        }

        $email = $data['email'];
        $password = $data['password'];

        // Find user by email, also get tenantId and role
        $stmt = $this->db->prepare("SELECT userId, tenantId, passwordHash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['passwordHash'])) {
            // In a real application, you would generate and return a JWT here
            $token = 'mock-token-' . $user['userId'];
            http_response_code(200);
            echo json_encode([
                'message' => 'Login successful!', 
                'token' => $token, 
                'userId' => $user['userId'], 
                'tenantId' => $user['tenantId'],
                'role' => $user['role']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password.']);
        }
    }
}
