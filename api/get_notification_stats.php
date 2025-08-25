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
    // Get subscriber count
    $subscriberStmt = $pdo->query("SELECT COUNT(*) as count FROM push_subscriptions WHERE endpoint IS NOT NULL");
    $subscriberCount = $subscriberStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get notifications sent today
    $todayStmt = $pdo->query("SELECT COUNT(*) as count FROM notification_log WHERE DATE(sent_at) = CURDATE()");
    $sentToday = $todayStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true,
        'subscribers' => $subscriberCount,
        'sent_today' => $sentToday
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get stats: ' . $e->getMessage()]);
}
?>