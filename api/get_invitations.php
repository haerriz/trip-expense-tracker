<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $userId = $_SESSION['user_id'];
    
    // Get pending invitations for current user
    $stmt = $pdo->prepare("
        SELECT tm.*, t.name as trip_name, u.name as invited_by_name 
        FROM trip_members tm 
        JOIN trips t ON tm.trip_id = t.id 
        JOIN users u ON tm.invited_by = u.id 
        WHERE tm.user_id = ? AND tm.status = 'pending'
        ORDER BY tm.invited_at DESC
    ");
    $stmt->execute([$userId]);
    $invitations = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'invitations' => $invitations]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>