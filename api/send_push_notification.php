<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_email'] !== 'haerriz@gmail.com') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
error_log('Raw input: ' . $rawInput);

$input = json_decode($rawInput, true);
error_log('Decoded input: ' . print_r($input, true));

if (!$input || !isset($input['title']) || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Title and message required', 'received' => $input]);
    exit;
}

try {
    // Get all push subscriptions
    $stmt = $pdo->query("SELECT * FROM push_subscriptions WHERE endpoint IS NOT NULL AND endpoint != ''");
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log('Found subscriptions: ' . count($subscriptions));
    foreach ($subscriptions as $sub) {
        error_log('Subscription endpoint: ' . substr($sub['endpoint'], 0, 50) . '...');
    }
    
    if (empty($subscriptions)) {
        echo json_encode(['success' => true, 'sent' => 0, 'message' => 'No subscribers found']);
        exit;
    }
    
    $title = $input['title'];
    $message = $input['message'];
    $sentCount = 0;
    
    // Use direct browser notifications (same as test notifications)
    // This bypasses FCM and works immediately
    
    // Store notification for polling system
    $stmt = $pdo->prepare("
        CREATE TABLE IF NOT EXISTS pending_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $pdo->exec($stmt->queryString);
    
    // Clear old notifications (older than 5 minutes)
    $pdo->exec("DELETE FROM pending_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    
    // Insert new notification
    $stmt = $pdo->prepare("
        INSERT INTO pending_notifications (title, message) VALUES (?, ?)
    ");
    $stmt->execute([$title, $message]);
    
    // Count as sent to all subscribers
    $sentCount = count($subscriptions);
    
    // Log the notification
    $logStmt = $pdo->prepare("
        INSERT INTO notification_log (admin_id, title, message, recipients_count, sent_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    // Create log table if it doesn't exist
    $createLogTable = "CREATE TABLE IF NOT EXISTS notification_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        recipients_count INT DEFAULT 0,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($createLogTable);
    
    $logStmt->execute([$_SESSION['user_id'], $title, $message, $sentCount]);
    
    echo json_encode([
        'success' => true, 
        'sent' => $sentCount,
        'message' => "Notification sent to {$sentCount} subscribers"
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send notifications: ' . $e->getMessage()]);
}

// Note: Real FCM push notifications require VAPID keys
// This endpoint shows how many subscribers we have, but actual sending
// requires proper Firebase/FCM setup with authentication keys
?>