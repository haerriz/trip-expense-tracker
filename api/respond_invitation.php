<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $invitationId = $_POST['invitation_id'] ?? '';
        $response = $_POST['response'] ?? ''; // 'accept' or 'reject'
        $userId = $_SESSION['user_id'];
        
        if (empty($invitationId) || empty($response)) {
            echo json_encode(['success' => false, 'message' => 'Invitation ID and response required']);
            exit;
        }
        
        if (!in_array($response, ['accept', 'reject'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid response']);
            exit;
        }
        
        // Verify invitation belongs to current user
        $stmt = $pdo->prepare("SELECT * FROM trip_members WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$invitationId, $userId]);
        $invitation = $stmt->fetch();
        
        if (!$invitation) {
            echo json_encode(['success' => false, 'message' => 'Invitation not found']);
            exit;
        }
        
        // Update invitation status
        $newStatus = $response === 'accept' ? 'accepted' : 'rejected';
        $joinedAt = $response === 'accept' ? 'NOW()' : 'NULL';
        
        $stmt = $pdo->prepare("UPDATE trip_members SET status = ?, joined_at = $joinedAt WHERE id = ?");
        $stmt->execute([$newStatus, $invitationId]);
        
        $message = $response === 'accept' ? 'Invitation accepted! You are now a member.' : 'Invitation declined.';
        echo json_encode(['success' => true, 'message' => $message]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>