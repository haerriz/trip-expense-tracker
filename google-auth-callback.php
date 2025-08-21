<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credential = $_POST['credential'] ?? '';
    
    if (empty($credential)) {
        header('Location: index.html?error=no_credential');
        exit;
    }
    
    // Decode JWT token
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        header('Location: index.html?error=invalid_token');
        exit;
    }
    
    $payload = json_decode(base64_decode($parts[1]), true);
    
    if (!$payload) {
        header('Location: index.html?error=invalid_payload');
        exit;
    }
    
    $email = $payload['email'] ?? '';
    $name = $payload['name'] ?? '';
    $picture = $payload['picture'] ?? '';
    
    if (empty($email) || empty($name)) {
        header('Location: index.html?error=missing_info');
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
        
        // Set session
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_picture'] = $picture;
        
        header('Location: dashboard.php');
        exit;
        
    } catch (Exception $e) {
        header('Location: index.html?error=database_error');
        exit;
    }
} else {
    header('Location: index.html');
    exit;
}
?>