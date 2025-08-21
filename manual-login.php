<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Debug logging
error_log('Login attempt - Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Login attempt - Content Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        error_log('Raw input: ' . $rawInput);
        
        $input = json_decode($rawInput, true);
        
        if (!$input) {
            error_log('JSON decode failed for: ' . $rawInput);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            exit;
        }
        
        error_log('Parsed input: ' . print_r($input, true));
        
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_picture'] = $user['picture'] ?: generateAvatar($user['name']);
            
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}

function generateAvatar($name) {
    $initials = strtoupper(substr($name, 0, 1));
    $colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#43e97b'];
    $color = $colors[array_rand($colors)];
    
    return "data:image/svg+xml;base64," . base64_encode("
        <svg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'>
            <rect width='40' height='40' fill='$color'/>
            <text x='20' y='25' font-family='Arial' font-size='16' fill='white' text-anchor='middle'>$initials</text>
        </svg>
    ");
}
?>