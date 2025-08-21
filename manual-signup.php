<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = $input['name'];
    $email = $input['email'];
    $password = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    // Create user
    $stmt = $pdo->prepare("INSERT INTO users (email, name, password) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$email, $name, $password])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
} else {
    echo json_encode(['success' => false]);
}
?>