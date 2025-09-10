<?php
// index.php - Maestro API Router

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-Tenant-Id");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include required classes
require_once '..\api\core\Database.php';
require_once '..\api\controllers\AuthController.php';
require_once '..\api\controllers\ProductsController.php';
require_once '..\api\controllers\ProductCategoriesController.php';
require_once '..\api\controllers\InventoryController.php';
require_once '..\api\controllers\SalesController.php';
require_once '..\api\controllers\CustomersController.php';

// Include the models that were created in the refactoring
require_once '..\api\models\AuthModel.php';
require_once '..\api\models\ProductCategoryModel.php';
require_once '..\api\models\InventoryModel.php';
require_once '..\api\models\SalesModel.php';
require_once '..\api\models\CustomersModel.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Strip the query string from the URI to get a clean path
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$path = trim($requestUri, '/');
$pathParts = explode('/', $path);

// Find the position of 'api' in the path
$apiIndex = array_search('api', $pathParts);
$resource = isset($pathParts[$apiIndex + 2]) ? $pathParts[$apiIndex + 2] : '';
$id = isset($pathParts[$apiIndex + 3]) ? $pathParts[$apiIndex + 3] : null;

// Routing logic based on the resource and HTTP method
switch ($resource) {
    case 'auth':
        $authModel = new AuthModel($db);
        $controller = new AuthController($authModel);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($id === 'register') {
                $controller->register();
            } elseif ($id === 'login') {
                $controller->login();
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Auth endpoint not found."]);
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
        }
        break;

    case 'products':
        $controller = new ProductsController($db);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if ($id) {
                    $controller->getProduct($id);
                } else {
                    $controller->getAllProducts();
                }
                break;
            case 'POST':
                $controller->createProduct();
                break;
            case 'PUT':
                if ($id) {
                    $controller->updateProduct($id);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing product ID.']);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $controller->deleteProduct($id);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing product ID.']);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method Not Allowed']);
                break;
        }
        break;
        
    case 'product-categories':
        $productCategoryModel = new ProductCategoryModel($db);
        $controller = new ProductCategoriesController($productCategoryModel);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                // Implement your GET logic here
                http_response_code(501);
                echo json_encode(['message' => 'Not Implemented']);
                break;
            case 'POST':
                $controller->createCategory();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method Not Allowed']);
                break;
        }
        break;

    case 'inventory':
        $inventoryModel = new InventoryModel($db);
        $controller = new InventoryController($inventoryModel);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $controller->getAllInventory();
                break;
            case 'POST':
                $controller->createInventoryItem();
                break;
            case 'PUT':
                if ($id) {
                    $controller->updateInventoryItem($id);
                } else {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing inventory ID."]);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $controller->deleteInventoryItem($id);
                } else {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing inventory ID."]);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method Not Allowed']);
                break;
        }
        break;

    case 'sales':
        $salesModel = new SalesModel($db);
        $controller = new SalesController($salesModel);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if ($id) {
                    $controller->getSale($id);
                } else {
                    $controller->getAllSales();
                }
                break;
            case 'POST':
                $controller->createSale();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method Not Allowed']);
                break;
        }
        break;

    case 'customers':
        $customersModel = new CustomersModel($db);
        $controller = new CustomersController($customersModel);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if ($id) {
                    $controller->getCustomer($id);
                } else {
                    $controller->getAllCustomers();
                }
                break;
            case 'POST':
                $controller->createCustomer();
                break;
            case 'PUT':
                if ($id) {
                    $controller->updateCustomer($id);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing customer ID.']);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $controller->deleteCustomer($id);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing customer ID.']);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method Not Allowed']);
                break;
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found."]);
        break;
}

$db->close();
