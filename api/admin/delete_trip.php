<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        
        if (empty($tripId)) {
            echo json_encode(['success' => false, 'message' => 'Trip ID required']);
            exit;
        }
        
        // Admin can delete any trip
        $pdo->beginTransaction();
        try {
            // Delete in proper order
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