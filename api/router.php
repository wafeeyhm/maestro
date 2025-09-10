<?php
// api/router.php

class Router {
    private $routes = [];

    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Remove the API base path from the URI
        $basePath = '/api/v1';
        if (strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        foreach ($this->routes as $route) {
            // Replace path parameters with a regex
            $pattern = str_replace(['{id}', '/'], ['([^/]+)', '\/'], $route['path']);
            $pattern = '/^' . $pattern . '$/';

            if (preg_match($pattern, $requestUri, $matches) && $route['method'] === $requestMethod) {
                // Get the ID if it exists
                $id = isset($matches[1]) ? $matches[1] : null;

                // Instantiate the controller and call the action
                $controller = new $route['controller']( (new Database())->getConnection() );
                
                if ($id) {
                    $controller->{$route['action']}($id);
                } else {
                    $controller->{$route['action']}();
                }
                return;
            }
        }

        // If no route is found, return 404
        http_response_code(404);
        echo json_encode(["error" => "Not Found"]);
    }
}
