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
    
    // Filter out trips where user is no longer a member (edge case cleanup)
    $validTrips = [];
    foreach ($trips as $trip) {
        // Double-check membership for non-creator trips
        if ($trip['created_by'] == $userId) {
            $validTrips[] = $trip;
        } else {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ? AND (status = 'accepted' OR status IS NULL)");
                $stmt->execute([$trip['id'], $userId]);
            } catch (Exception $e) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ?");
                $stmt->execute([$trip['id'], $userId]);
            }
            
            if ($stmt->fetchColumn() > 0) {
                $validTrips[] = $trip;
            }
        }
    }
    
    echo json_encode(['success' => true, 'trips' => $validTrips]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>