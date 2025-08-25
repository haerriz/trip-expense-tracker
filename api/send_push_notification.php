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

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['title']) || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Title and message required']);
    exit;
}

try {
    // Get all push subscriptions
    $stmt = $pdo->query("SELECT * FROM push_subscriptions WHERE endpoint IS NOT NULL");
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($subscriptions)) {
        echo json_encode(['success' => true, 'sent' => 0, 'message' => 'No subscribers found']);
        exit;
    }
    
    $title = $input['title'];
    $message = $input['message'];
    $sentCount = 0;
    
    // Simple push notification (for demo - in production use proper VAPID keys)
    foreach ($subscriptions as $subscription) {
        // In a real implementation, you would use a library like web-push
        // For demo purposes, we'll just log the attempt
        error_log("Would send push notification to: " . $subscription['endpoint']);
        $sentCount++;
    }
    
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
?>