<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_email'] !== 'haerriz@gmail.com') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT title, message, recipients_count, sent_at 
        FROM notification_log 
        ORDER BY sent_at DESC 
        LIMIT 10
    ");
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get history: ' . $e->getMessage()]);
}
?>