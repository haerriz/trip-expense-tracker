<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit();
        } else {
            header('Location: index.html');
            exit();
        }
    }
}

function loginUser($googleData) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
    $stmt->execute([$googleData['id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $stmt = $pdo->prepare("INSERT INTO users (google_id, email, name, picture) VALUES (?, ?, ?, ?)");
        $stmt->execute([$googleData['id'], $googleData['email'], $googleData['name'], $googleData['picture']]);
        $userId = $pdo->lastInsertId();
    } else {
        $userId = $user['id'];
    }
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $googleData['name'];
    $_SESSION['user_email'] = $googleData['email'];
    $_SESSION['user_picture'] = $googleData['picture'];
}

function logout() {
    session_destroy();
    header('Location: index.html');
    exit();
}
?>