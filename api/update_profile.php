<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    
    try {
        // Get current user data
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        // Validate current password if changing password
        if ($newPassword && !password_verify($currentPassword, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }
        
        // Handle avatar upload
        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = $userId . '_' . time() . '.' . $fileExtension;
            $avatarPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath)) {
                $avatarPath = 'uploads/avatars/' . $fileName;
            } else {
                $avatarPath = null;
            }
        }
        
        // Update user profile
        $updateFields = ['name = ?', 'email = ?'];
        $updateValues = [$name, $email];
        
        if ($phone) {
            $updateFields[] = 'phone = ?';
            $updateValues[] = $phone;
        }
        
        if ($newPassword) {
            $updateFields[] = 'password = ?';
            $updateValues[] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        if ($avatarPath) {
            $updateFields[] = 'picture = ?';
            $updateValues[] = $avatarPath;
        }
        
        $updateValues[] = $userId;
        
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateValues);
        
        // Update session data
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        if ($avatarPath) {
            $_SESSION['user_picture'] = $avatarPath;
        }
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>