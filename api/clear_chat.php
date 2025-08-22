<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId)) {
            echo json_encode(['success' => false, 'error' => 'Trip ID required']);
            exit;
        }
        
        // Check if user is trip owner or admin
        $stmt = $pdo->prepare("SELECT created_by FROM trips WHERE id = ?");
        $stmt->execute([$tripId]);
        $trip = $stmt->fetch();
        
        if (!$trip || ($trip['created_by'] != $userId && $_SESSION['user_email'] !== 'haerriz@gmail.com')) {
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        
        // Clear chat messages for this trip
        $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE trip_id = ?");
        
        if ($stmt->execute([$tripId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to clear chat']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>