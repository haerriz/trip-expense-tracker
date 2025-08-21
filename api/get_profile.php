<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT name, email, phone, phone_verified, picture FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>