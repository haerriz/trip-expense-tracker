<?php
// Manual cleanup script for orphaned trips
require_once 'config/database.php';

echo "<h2>Orphaned Trip Cleanup</h2>";

try {
    // Find trips with no members
    try {
        $stmt = $pdo->prepare("
            SELECT t.id, t.name, t.created_by, t.created_at,
                   COUNT(tm.user_id) as member_count
            FROM trips t 
            LEFT JOIN trip_members tm ON t.id = tm.trip_id AND (tm.status = 'accepted' OR tm.status IS NULL)
            GROUP BY t.id 
            HAVING COUNT(tm.user_id) = 0
        ");
        $stmt->execute();
    } catch (Exception $e) {
        // Fallback for old schema
        $stmt = $pdo->prepare("
            SELECT t.id, t.name, t.created_by, t.created_at,
                   COUNT(tm.user_id) as member_count
            FROM trips t 
            LEFT JOIN trip_members tm ON t.id = tm.trip_id 
            GROUP BY t.id 
            HAVING COUNT(tm.user_id) = 0
        ");
        $stmt->execute();
    }
    
    $orphanedTrips = $stmt->fetchAll();
    
    if (empty($orphanedTrips)) {
        echo "<p>✅ No orphaned trips found!</p>";
    } else {
        echo "<p>Found " . count($orphanedTrips) . " orphaned trips:</p>";
        echo "<ul>";
        
        foreach ($orphanedTrips as $trip) {
            echo "<li>Trip ID: {$trip['id']} - '{$trip['name']}' (Created: {$trip['created_at']})</li>";
        }
        
        echo "</ul>";
        
        if (isset($_GET['cleanup']) && $_GET['cleanup'] === 'true') {
            echo "<h3>Cleaning up orphaned trips...</h3>";
            $cleanedCount = 0;
            
            foreach ($orphanedTrips as $trip) {
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
                    echo "<p>✅ Cleaned up trip: {$trip['name']}</p>";
                    $cleanedCount++;
                } catch (Exception $e) {
                    $pdo->rollback();
                    echo "<p>❌ Failed to cleanup trip {$trip['name']}: " . $e->getMessage() . "</p>";
                }
            }
            
            echo "<h3>🎉 Cleanup completed! Removed {$cleanedCount} orphaned trips.</h3>";
        } else {
            echo "<p><a href='?cleanup=true' onclick='return confirm(\"Are you sure you want to delete all orphaned trips?\")'>🗑️ Click here to cleanup orphaned trips</a></p>";
        }
    }
    
    echo "<p><a href='/dashboard.php'>← Back to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>