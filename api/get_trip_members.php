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
    
    // Get trip members
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.picture, tm.joined_at
        FROM trip_members tm 
        JOIN users u ON tm.user_id = u.id 
        WHERE tm.trip_id = ?
        ORDER BY tm.joined_at ASC
    ");
    $stmt->execute([$tripId]);
    $members = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'members' => $members
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>