<?php

require_once __DIR__ . '/../controllers/TaskController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//$uri    = rtrim($uri, '/');

// Strip /public from URI if running via built-in server from /public
$uri = preg_replace('#^/public#', '', $uri);

$controller = new TaskController();

// GET /api/tasks/report
if ($method === 'GET' && $uri === '/api/tasks/report') {
    $controller->report();
}

// GET /api/tasks
if ($method === 'GET' && $uri === '/api/tasks') {
    $controller->index();
}

// POST /api/tasks
if ($method === 'POST' && $uri === '/api/tasks') {
    $controller->create();
}

// PATCH /api/tasks/{id}/status
if ($method === 'PATCH' && preg_match('#^/api/tasks/(\d+)/status$#', $uri, $m)) {
    $controller->updateStatus((int) $m[1]);
}

// DELETE /api/tasks/{id}
if ($method === 'DELETE' && preg_match('#^/api/tasks/(\d+)$#', $uri, $m)) {
    $controller->delete((int) $m[1]);
}

http_response_code(404);
echo json_encode(['error' => 'Route not found.']);
