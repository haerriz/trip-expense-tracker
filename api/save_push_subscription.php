<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['subscription'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Subscription data required']);
    exit;
}

try {
    $subscription = $input['subscription'];
    $user_id = $input['user_id'] ?? null;
    
    // Create push_subscriptions table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS push_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        endpoint TEXT NOT NULL,
        p256dh_key TEXT NOT NULL,
        auth_key TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_endpoint (user_id, endpoint(100))
    )";
    
    $pdo->exec($createTable);
    
    // Save subscription (use REPLACE to handle duplicates)
    $stmt = $pdo->prepare("
        REPLACE INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user_id,
        $subscription['endpoint'],
        $subscription['keys']['p256dh'],
        $subscription['keys']['auth']
    ]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log('Push subscription error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save subscription']);
}
?>