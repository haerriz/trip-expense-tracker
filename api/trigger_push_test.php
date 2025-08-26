<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_email'] !== 'haerriz@gmail.com') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$title = $input['title'] ?? 'Test Notification';
$message = $input['message'] ?? 'This is a test message';

// For testing, we'll use a simple approach to trigger service worker
// This creates a server-sent event that the client can listen to
echo json_encode([
    'success' => true,
    'action' => 'trigger_sw_notification',
    'data' => [
        'title' => $title,
        'body' => $message,
        'icon' => '/favicon.svg',
        'badge' => '/favicon.svg'
    ]
]);
?>