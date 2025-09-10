<?php
// api/controllers/AuthController.php

require_once __DIR__ . '/../models/AuthModel.php';
require_once __DIR__ . '/../models/LogModel.php';

class AuthController {
    private $authModel;
    private $logModel;

    public function __construct(AuthModel $authModel, LogModel $logModel) {
        $this->authModel = $authModel;
        $this->logModel = $logModel;
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['tenantId']) || !isset($data['email']) || !isset($data['password']) || !isset($data['firstName']) || !isset($data['lastName']) || !isset($data['role'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All required fields are not provided.']);
            return;
        }
        
        if (!$this->authModel->tenantExists($data['tenantId'])) {
            http_response_code(404);
            echo json_encode(['error' => 'Tenant not found. You can only register users to an existing cafe.']);
            return;
        }
        
        if ($this->authModel->findUserByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already registered.']);
            return;
        }
        
        $userId = $this->authModel->registerUser($data);
        
        if ($userId) {
            http_response_code(201);
            echo json_encode(['message' => 'User registered successfully!', 'userId' => $userId, 'tenantId' => $data['tenantId']]);

            // Log the successful registration
            $this->logModel->logAction(
                $data['tenantId'], 
                $userId, 
                'user_registered', 
                ['email' => $data['email'], 'role' => $data['role']]
            );
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to register user.']);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required.']);
            return;
        }
        
        $user = $this->authModel->findUserByEmail($data['email']);

        if ($user && password_verify($data['password'], $user['passwordHash'])) {
            $token = 'mock-token-' . $user['userId'];
            http_response_code(200);
            echo json_encode([
                'message' => 'Login successful!', 
                'token' => $token, 
                'userId' => $user['userId'], 
                'tenantId' => $user['tenantId'],
                'role' => $user['role']
            ]);

            // Log the successful login
            $this->logModel->logAction(
                $user['tenantId'], 
                $user['userId'], 
                'user_login', 
                ['email' => $data['email']]
            );
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password.']);
        }
    }
}
