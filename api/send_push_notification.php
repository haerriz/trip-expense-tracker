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
    $stmt = $pdo->query("SELECT * FROM push_subscriptions WHERE endpoint IS NOT NULL");
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($subscriptions)) {
        echo json_encode(['success' => true, 'sent' => 0, 'message' => 'No subscribers found']);
        exit;
    }
    
    $title = $input['title'];
    $message = $input['message'];
    $sentCount = 0;
    
    // Send actual push notifications
    foreach ($subscriptions as $subscription) {
        try {
            $success = sendWebPushNotification(
                $subscription['endpoint'],
                $subscription['p256dh_key'],
                $subscription['auth_key'],
                json_encode([
                    'title' => $title,
                    'body' => $message,
                    'icon' => '/favicon.svg',
                    'badge' => '/favicon.svg'
                ])
            );
            
            if ($success) {
                $sentCount++;
            }
        } catch (Exception $e) {
            error_log("Failed to send push to {$subscription['endpoint']}: " . $e->getMessage());
        }
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

// Simple web push function (basic implementation)
function sendWebPushNotification($endpoint, $p256dh, $auth, $payload) {
    // For demo purposes, we'll use a simple HTTP request
    // In production, use a proper library like web-push-php
    
    $headers = [
        'Content-Type: application/json',
        'TTL: 86400'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}
?>