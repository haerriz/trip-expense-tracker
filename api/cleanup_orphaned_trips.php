<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Find trips with no members
    try {
        $stmt = $pdo->prepare("
            SELECT t.id, t.name, t.created_by 
            FROM trips t 
            LEFT JOIN trip_members tm ON t.id = tm.trip_id AND (tm.status = 'accepted' OR tm.status IS NULL)
            GROUP BY t.id 
            HAVING COUNT(tm.user_id) = 0
        ");
        $stmt->execute();
    } catch (Exception $e) {
        // Fallback for old schema
        $stmt = $pdo->prepare("
            SELECT t.id, t.name, t.created_by 
            FROM trips t 
            LEFT JOIN trip_members tm ON t.id = tm.trip_id 
            GROUP BY t.id 
            HAVING COUNT(tm.user_id) = 0
        ");
        $stmt->execute();
    }
    
    $orphanedTrips = $stmt->fetchAll();
    $cleanedCount = 0;
    
    foreach ($orphanedTrips as $trip) {
        // Delete orphaned trip and all related data
        $pdo->beginTransaction();
        try {
            // Delete in proper order
            $stmt = $pdo->prepare("DELETE FROM expense_splits WHERE expense_id IN (SELECT id FROM expenses WHERE trip_id = ?)");
            $stmt->execute([$trip['id']]);
            
            $stmt = $pdo->prepare("DELETE FROM expenses WHERE trip_id = ?");
            $stmt->execute([$trip['id']]);
            
            $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE trip_id = ?");
            $stmt->execute([$trip['id']]);
            
            $stmt = $pdo->prepare("DELETE FROM trip_members WHERE trip_id = ?");
            $stmt->execute([$trip['id']]);
            
            $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ?");
            $stmt->execute([$trip['id']]);
            
            $pdo->commit();
            $cleanedCount++;
        } catch (Exception $e) {
            $pdo->rollback();
            error_log("Failed to cleanup trip {$trip['id']}: " . $e->getMessage());
        }
    }
    
    echo json_encode([
        'success' => true, 
        'cleaned_trips' => $cleanedCount,
        'message' => $cleanedCount > 0 ? "Cleaned up {$cleanedCount} orphaned trips" : "No orphaned trips found"
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Cleanup error: ' . $e->getMessage()]);
}
?>