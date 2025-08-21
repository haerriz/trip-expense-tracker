<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $message = $_POST['message'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId) || empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Trip ID and message required']);
            exit;
        }
        
        // Insert chat message
        $stmt = $pdo->prepare("INSERT INTO chat_messages (trip_id, user_id, message) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$tripId, $userId, $message])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>