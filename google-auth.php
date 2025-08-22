<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $credential = $input['credential'] ?? '';
    
    if (empty($credential)) {
        echo json_encode(['success' => false, 'message' => 'No credential provided']);
        exit;
    }
    
    // Decode JWT token (simple decode without verification for demo)
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        echo json_encode(['success' => false, 'message' => 'Invalid token format']);
        exit;
    }
    
    $payload = json_decode(base64_decode($parts[1]), true);
    
    if (!$payload) {
        echo json_encode(['success' => false, 'message' => 'Invalid token payload']);
        exit;
    }
    
    $email = $payload['email'] ?? '';
    $name = $payload['name'] ?? '';
    $picture = $payload['picture'] ?? '';
    
    if (empty($email) || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Missing user information']);
        exit;
    }
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update existing user
            $stmt = $pdo->prepare("UPDATE users SET name = ?, picture = ? WHERE email = ?");
            $stmt->execute([$name, $picture, $email]);
            $userId = $user['id'];
        } else {
            // Create new user
            $stmt = $pdo->prepare("INSERT INTO users (email, name, picture, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $name, $picture, 'google_oauth']);
            $userId = $pdo->lastInsertId();
        }
        
        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_picture'] = $picture;
        
        // Force session write
        session_write_close();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Authentication successful',
            'redirect' => 'dashboard.php'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>