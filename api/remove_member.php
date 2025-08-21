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
        
        // Check if current user is trip creator OR the member is removing themselves
        $stmt = $pdo->prepare("SELECT created_by FROM trips WHERE id = ?");
        $stmt->execute([$tripId]);
        $trip = $stmt->fetch();
        
        $isCreator = ($trip && $trip['created_by'] == $userId);
        $isSelfRemoval = ($memberId == $userId);
        
        if (!$isCreator && !$isSelfRemoval) {
            echo json_encode(['success' => false, 'message' => 'Only trip creator or the member themselves can remove membership']);
            exit;
        }
        
        // Check if member has any expenses or splits (only if not self-removal)
        if (!$isSelfRemoval) {
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
                // If expense_splits table doesn't exist, just check expenses
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
            echo json_encode(['success' => true, 'message' => $message]);
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