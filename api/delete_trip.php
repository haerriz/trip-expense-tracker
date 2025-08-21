<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId)) {
            echo json_encode(['success' => false, 'message' => 'Trip ID required']);
            exit;
        }
        
        // Verify user is trip creator
        $stmt = $pdo->prepare("SELECT created_by, name FROM trips WHERE id = ?");
        $stmt->execute([$tripId]);
        $trip = $stmt->fetch();
        
        if (!$trip) {
            echo json_encode(['success' => false, 'message' => 'Trip not found']);
            exit;
        }
        
        if ($trip['created_by'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Only trip creator can delete the trip']);
            exit;
        }
        
        // Check if trip has expenses
        $stmt = $pdo->prepare("SELECT COUNT(*) as expense_count FROM expenses WHERE trip_id = ?");
        $stmt->execute([$tripId]);
        $expenseCount = $stmt->fetchColumn();
        
        if ($expenseCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete trip with existing expenses. Remove all expenses first.']);
            exit;
        }
        
        // Delete trip and related data
        $pdo->beginTransaction();
        try {
            // Delete in proper order
            $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE trip_id = ?");
            $stmt->execute([$tripId]);
            
            $stmt = $pdo->prepare("DELETE FROM trip_members WHERE trip_id = ?");
            $stmt->execute([$tripId]);
            
            $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ?");
            $stmt->execute([$tripId]);
            
            $pdo->commit();
            
            echo json_encode(['success' => true, 'message' => 'Trip deleted successfully']);
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to delete trip: ' . $e->getMessage()]);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>