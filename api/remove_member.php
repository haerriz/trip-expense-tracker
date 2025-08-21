<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $memberId = $_POST['member_id'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId) || empty($memberId)) {
            echo json_encode(['success' => false, 'message' => 'Trip ID and member ID required']);
            exit;
        }
        
        // Get trip details
        $stmt = $pdo->prepare("SELECT created_by, name FROM trips WHERE id = ?");
        $stmt->execute([$tripId]);
        $trip = $stmt->fetch();
        
        if (!$trip) {
            echo json_encode(['success' => false, 'message' => 'Trip not found']);
            exit;
        }
        
        $isCreator = ($trip['created_by'] == $userId);
        $isSelfRemoval = ($memberId == $userId);
        $isCreatorLeaving = ($isCreator && $isSelfRemoval);
        
        // Permission check
        if (!$isCreator && !$isSelfRemoval) {
            echo json_encode(['success' => false, 'message' => 'Only trip creator or the member themselves can remove membership']);
            exit;
        }
        
        // Special handling for trip creator leaving
        if ($isCreatorLeaving) {
            // Count other members
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) as member_count FROM trip_members WHERE trip_id = ? AND user_id != ? AND (status = 'accepted' OR status IS NULL)");
                $stmt->execute([$tripId, $userId]);
            } catch (Exception $e) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as member_count FROM trip_members WHERE trip_id = ? AND user_id != ?");
                $stmt->execute([$tripId, $userId]);
            }
            $memberCount = $stmt->fetchColumn();
            
            if ($memberCount > 0) {
                // Transfer ownership to oldest member
                try {
                    $stmt = $pdo->prepare("
                        SELECT user_id FROM trip_members 
                        WHERE trip_id = ? AND user_id != ? AND (status = 'accepted' OR status IS NULL)
                        ORDER BY joined_at ASC, id ASC 
                        LIMIT 1
                    ");
                    $stmt->execute([$tripId, $userId]);
                } catch (Exception $e) {
                    $stmt = $pdo->prepare("
                        SELECT user_id FROM trip_members 
                        WHERE trip_id = ? AND user_id != ?
                        ORDER BY id ASC 
                        LIMIT 1
                    ");
                    $stmt->execute([$tripId, $userId]);
                }
                $newOwner = $stmt->fetchColumn();
                
                if ($newOwner) {
                    // Transfer ownership
                    $stmt = $pdo->prepare("UPDATE trips SET created_by = ? WHERE id = ?");
                    $stmt->execute([$newOwner, $tripId]);
                    
                    // Remove the creator from members
                    $stmt = $pdo->prepare("DELETE FROM trip_members WHERE trip_id = ? AND user_id = ?");
                    $stmt->execute([$tripId, $userId]);
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'You have left the trip. Ownership has been transferred to another member.',
                        'action' => 'ownership_transferred'
                    ]);
                    exit;
                }
            }
            
            // No other members - delete the entire trip
            $pdo->beginTransaction();
            try {
                // Delete in proper order to maintain referential integrity
                $stmt = $pdo->prepare("DELETE FROM expense_splits WHERE expense_id IN (SELECT id FROM expenses WHERE trip_id = ?)");
                $stmt->execute([$tripId]);
                
                $stmt = $pdo->prepare("DELETE FROM expenses WHERE trip_id = ?");
                $stmt->execute([$tripId]);
                
                $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE trip_id = ?");
                $stmt->execute([$tripId]);
                
                $stmt = $pdo->prepare("DELETE FROM trip_members WHERE trip_id = ?");
                $stmt->execute([$tripId]);
                
                $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ?");
                $stmt->execute([$tripId]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Trip has been deleted as you were the only member.',
                    'action' => 'trip_deleted'
                ]);
                exit;
            } catch (Exception $e) {
                $pdo->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to delete trip: ' . $e->getMessage()]);
                exit;
            }
        }
        
        // Regular member removal (not creator leaving)
        if (!$isSelfRemoval) {
            // Check if member has expenses
            try {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as expense_count 
                    FROM expenses e 
                    LEFT JOIN expense_splits es ON e.id = es.expense_id 
                    WHERE e.trip_id = ? AND (e.paid_by = ? OR es.user_id = ?)
                ");
                $stmt->execute([$tripId, $memberId, $memberId]);
                $result = $stmt->fetch();
                
                if ($result['expense_count'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'Cannot remove member with existing expenses or splits']);
                    exit;
                }
            } catch (Exception $e) {
                // Fallback for old schema
                $stmt = $pdo->prepare("SELECT COUNT(*) as expense_count FROM expenses WHERE trip_id = ? AND paid_by = ?");
                $stmt->execute([$tripId, $memberId]);
                $result = $stmt->fetch();
                
                if ($result['expense_count'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'Cannot remove member with existing expenses']);
                    exit;
                }
            }
        }
        
        // Remove member
        $stmt = $pdo->prepare("DELETE FROM trip_members WHERE trip_id = ? AND user_id = ?");
        
        if ($stmt->execute([$tripId, $memberId])) {
            $message = $isSelfRemoval ? 'You have left the trip' : 'Member removed successfully';
            echo json_encode(['success' => true, 'message' => $message, 'action' => 'member_removed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove member']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>