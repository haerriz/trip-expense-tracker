<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit();
        } else {
            header('Location: index.php');
            exit();
        }
    }
}

function loginUser($googleData) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->execute([$googleData['id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Try to find by email first
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$googleData['email']]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Update existing user with Google ID
                $stmt = $pdo->prepare("UPDATE users SET google_id = ?, name = ?, picture = ? WHERE id = ?");
                $stmt->execute([$googleData['id'], $googleData['name'], $googleData['picture'], $user['id']]);
                $userId = $user['id'];
            } else {
                // Create new user
                $stmt = $pdo->prepare("INSERT INTO users (google_id, email, name, picture, password) VALUES (?, ?, ?, ?, 'google_oauth')");
                $stmt->execute([$googleData['id'], $googleData['email'], $googleData['name'], $googleData['picture']]);
                $userId = $pdo->lastInsertId();
            }
        } else {
            $userId = $user['id'];
        }
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $googleData['name'];
        $_SESSION['user_email'] = $googleData['email'];
        $_SESSION['user_picture'] = $googleData['picture'];
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}

function getUserById($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return false;
    }
}
?>