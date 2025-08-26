<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_email'] !== 'haerriz@gmail.com') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'API is working',
    'user' => $_SESSION['user_email'],
    'method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s')
]);
?>