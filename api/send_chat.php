<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $chatToken = $_SESSION['chat_csrf'] ?? null;
    $submitted = $_POST['csrf'] ?? null;
    if (!is_string($chatToken) || !is_string($submitted) || !hash_equals($chatToken, $submitted)) {
        http_response_code(403);
        exit(json_encode(['success' => false, 'error' => 'Refresh your dashboard before trying again.']));
    }
        $tripId = $_POST['trip_id'] ?? '';
        requireTripAccess($tripId);
        $message = $_POST['message'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (!is_string($message) || trim($message) === '' || mb_strlen($message) > 4000) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Enter a message up to 4,000 characters.']);
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
} catch (Throwable $e) {
    error_log('Chat send failed: ' . get_class($e));
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to send the message.']);
}
?>