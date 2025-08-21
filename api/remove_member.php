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
        
        // Check if current user is trip creator
        $stmt = $pdo->prepare("SELECT created_by FROM trips WHERE id = ?");
        $stmt->execute([$tripId]);
        $trip = $stmt->fetch();
        
        if (!$trip || $trip['created_by'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Only trip creator can remove members']);
            exit;
        }
        
        // Check if member has any expenses or splits
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
        
        // Remove member
        $stmt = $pdo->prepare("DELETE FROM trip_members WHERE trip_id = ? AND user_id = ?");
        
        if ($stmt->execute([$tripId, $memberId])) {
            echo json_encode(['success' => true, 'message' => 'Member removed successfully']);
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