<?php
// api/controllers/AuthController.php

require_once __DIR__ . '/../models/AuthModel.php';

class AuthController {
    private $authModel;

    public function __construct($authModel) {
        $this->authModel = $authModel;
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Basic validation
        if (!isset($data['tenantId']) || !isset($data['email']) || !isset($data['password']) || !isset($data['firstName']) || !isset($data['lastName']) || !isset($data['role'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All required fields are not provided.']);
            return;
        }
        
        // Use the model to check if the tenant exists
        if (!$this->authModel->tenantExists($data['tenantId'])) {
            http_response_code(404);
            echo json_encode(['error' => 'Tenant not found. You can only register users to an existing cafe.']);
            return;
        }
        
        // Use the model to check if the user already exists
        if ($this->authModel->findUserByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already registered.']);
            return;
        }
        
        // Use the model to register the new user
        $userId = $this->authModel->registerUser($data);
        
        if ($userId) {
            http_response_code(201);
            echo json_encode(['message' => 'User registered successfully!', 'userId' => $userId, 'tenantId' => $data['tenantId']]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to register user.']);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Basic validation
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required.']);
            return;
        }
        
        // Use the model to find the user
        $user = $this->authModel->findUserByEmail($data['email']);

        if ($user && password_verify($data['password'], $user['passwordHash'])) {
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
