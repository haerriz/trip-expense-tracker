<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $tripId = $_GET['trip_id'];
    
    if (!$tripId) {
        echo json_encode(['success' => false, 'error' => 'Trip ID required']);
        exit;
    }
    
    // Get chat messages
    $stmt = $pdo->prepare("
        SELECT cm.*, u.name as sender_name 
        FROM chat_messages cm 
        JOIN users u ON cm.user_id = u.id 
        WHERE cm.trip_id = ? 
        ORDER BY cm.created_at ASC 
        LIMIT 50
    ");
    $stmt->execute([$tripId]);
    $messages = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>