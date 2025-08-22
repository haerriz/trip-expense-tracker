<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId)) {
            echo json_encode(['success' => false, 'error' => 'Trip ID required']);
            exit;
        }
        
        if (!isset($_FILES['file'])) {
            echo json_encode(['success' => false, 'error' => 'No file uploaded']);
            exit;
        }
        
        $file = $_FILES['file'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'File type not allowed']);
            exit;
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'File too large (max 5MB)']);
            exit;
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = '../uploads/chat/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Save file info to database (you'd need to create a chat_files table)
            $fileUrl = '/uploads/chat/' . $filename;
            $originalName = $file['name'];
            
            // For now, send as a regular message with file info
            $message = "📎 Shared a file: " . $originalName;
            
            $stmt = $pdo->prepare("INSERT INTO chat_messages (trip_id, user_id, message, file_url, file_name) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$tripId, $userId, $message, $fileUrl, $originalName])) {
                echo json_encode([
                    'success' => true,
                    'file_url' => $fileUrl,
                    'file_name' => $originalName
                ]);
            } else {
                // Clean up uploaded file if database insert fails
                unlink($filepath);
                echo json_encode(['success' => false, 'error' => 'Failed to save file info']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>