<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tripId = $_POST['trip_id'];
    $message = trim($_POST['message']);
    $userId = $_SESSION['user_id'];
    
    if (empty($message) || strlen($message) > 500) {
        echo json_encode(['success' => false, 'message' => 'Invalid message']);
        exit;
    }
    
    // Check if user is member of trip
    $stmt = $pdo->prepare("SELECT id FROM trip_members WHERE trip_id = ? AND user_id = ?");
    $stmt->execute([$tripId, $userId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Not a member of this trip']);
        exit;
    }
    
    // Add message
    $stmt = $pdo->prepare("INSERT INTO trip_chat (trip_id, user_id, message) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$tripId, $userId, $message])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>