<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tripId = $_POST['trip_id'];
    $email = $_POST['email'];
    
    // Find user by email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found. They need to register first.']);
        exit;
    }
    
    // Check if already a member
    $stmt = $pdo->prepare("SELECT id FROM trip_members WHERE trip_id = ? AND user_id = ?");
    $stmt->execute([$tripId, $user['id']]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'User is already a member of this trip.']);
        exit;
    }
    
    // Add member
    $stmt = $pdo->prepare("INSERT INTO trip_members (trip_id, user_id) VALUES (?, ?)");
    
    if ($stmt->execute([$tripId, $user['id']])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add member.']);
    }
} else {
    echo json_encode(['success' => false]);
}
?>