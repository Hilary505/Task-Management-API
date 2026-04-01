<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Let PHP built-in server serve static files (html, css, js)
if ($uri === '' || $uri === '/index.html') {
    header('Content-Type: text/html');
    readfile(__DIR__ . '/index.html');
    exit;
}

if (!str_starts_with($uri, '/api')) {
    return false; // serve the file as-is
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../routes/api.php';
