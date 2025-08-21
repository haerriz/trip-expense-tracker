<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT DISTINCT t.* FROM trips t 
        LEFT JOIN trip_members tm ON t.id = tm.trip_id 
        WHERE t.created_by = ? OR tm.user_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$userId, $userId]);
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['trips' => $trips]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>