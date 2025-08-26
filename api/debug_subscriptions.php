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
    // Check table structure
    $stmt = $pdo->query("DESCRIBE push_subscriptions");
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all subscriptions
    $stmt = $pdo->query("SELECT * FROM push_subscriptions");
    $all_subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get non-empty subscriptions
    $stmt = $pdo->query("SELECT * FROM push_subscriptions WHERE endpoint IS NOT NULL AND endpoint != ''");
    $valid_subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'table_structure' => $structure,
        'all_subscriptions' => $all_subs,
        'valid_subscriptions' => $valid_subs,
        'counts' => [
            'total' => count($all_subs),
            'valid' => count($valid_subs)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>