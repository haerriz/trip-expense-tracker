<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $newOwnerId = $_POST['new_owner_id'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId) || empty($newOwnerId)) {
            echo json_encode(['success' => false, 'message' => 'Trip ID and new owner ID required']);
            exit;
        }
        
        // Verify current user is trip creator
        $stmt = $pdo->prepare("SELECT created_by, name FROM trips WHERE id = ?");
        $stmt->execute([$tripId]);
        $trip = $stmt->fetch();
        
        if (!$trip) {
            echo json_encode(['success' => false, 'message' => 'Trip not found']);
            exit;
        }
        
        if ($trip['created_by'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Only trip creator can transfer ownership']);
            exit;
        }
        
        // Verify new owner is a member of the trip
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM trip_members WHERE trip_id = ? AND user_id = ? AND (status = 'accepted' OR status IS NULL)");
            $stmt->execute([$tripId, $newOwnerId]);
        } catch (Exception $e) {
            $stmt = $pdo->prepare("SELECT user_id FROM trip_members WHERE trip_id = ? AND user_id = ?");
            $stmt->execute([$tripId, $newOwnerId]);
        }
        
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'New owner must be an accepted member of the trip']);
            exit;
        }
        
        // Transfer ownership
        $stmt = $pdo->prepare("UPDATE trips SET created_by = ? WHERE id = ?");
        
        if ($stmt->execute([$newOwnerId, $tripId])) {
            // Get new owner name
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$newOwnerId]);
            $newOwnerName = $stmt->fetchColumn();
            
            echo json_encode([
                'success' => true, 
                'message' => "Ownership transferred to {$newOwnerName} successfully"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to transfer ownership']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>