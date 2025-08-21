<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $email = $_POST['email'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Trip ID and email required']);
            exit;
        }
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $invitedUser = $stmt->fetch();
        
        if (!$invitedUser) {
            echo json_encode(['success' => false, 'message' => 'User not found. They need to create an account first.']);
            exit;
        }
        
        // Check if already a member
        $stmt = $pdo->prepare("SELECT status FROM trip_members WHERE trip_id = ? AND user_id = ?");
        $stmt->execute([$tripId, $invitedUser['id']]);
        $existing = $stmt->fetch();
        
        if ($existing && $existing['status'] === 'accepted') {
            echo json_encode(['success' => false, 'message' => 'User is already a member']);
            exit;
        }
        
        // Send invitation (pending status)
        if ($existing) {
            $stmt = $pdo->prepare("UPDATE trip_members SET status = 'pending', invited_by = ? WHERE trip_id = ? AND user_id = ?");
            $stmt->execute([$userId, $tripId, $invitedUser['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO trip_members (trip_id, user_id, status, invited_by) VALUES (?, ?, 'pending', ?)");
            $stmt->execute([$tripId, $invitedUser['id'], $userId]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Invitation sent to ' . $invitedUser['name']]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>