<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $userId = $_SESSION['user_id'];
    
    // Get trips where user is creator or accepted member
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT t.* FROM trips t 
            LEFT JOIN trip_members tm ON t.id = tm.trip_id 
            WHERE t.created_by = ? OR (tm.user_id = ? AND (tm.status = 'accepted' OR tm.status IS NULL))
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$userId, $userId]);
    } catch (Exception $e) {
        // Fallback for old schema without status column
        $stmt = $pdo->prepare("
            SELECT DISTINCT t.* FROM trips t 
            LEFT JOIN trip_members tm ON t.id = tm.trip_id 
            WHERE t.created_by = ? OR tm.user_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$userId, $userId]);
    }
    
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'trips' => $trips]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>