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
        if (($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null)
            || !is_uploaded_file($file['tmp_name']) || ($file['size'] ?? 0) < 1) {
            http_response_code(422);
            exit(json_encode(['success' => false, 'error' => 'Choose a valid attachment.']));
        }
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        $detectedType = $file['error'] === UPLOAD_ERR_OK
            ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
        $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','application/pdf'=>'pdf','application/msword'=>'doc','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx'];
        $extension = $extensions[$detectedType] ?? null;
        if (!$extension || !in_array($detectedType, $allowedTypes, true)) {
            echo json_encode(['success' => false, 'error' => 'File type not allowed']);
            exit;
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'File too large (max 5MB)']);
            exit;
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = dirname(__DIR__) . '/uploads/chat/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Save only files accepted by PHP's upload handler.
        $saved = false;
        if (is_uploaded_file($file['tmp_name'])) {
            $saved = copy($file['tmp_name'], $filepath);
            if (!$saved && is_file($filepath)) {
                unlink($filepath);
            }
        }
        if ($saved) {
            // Save file info to database (you'd need to create a chat_files table)
            $fileUrl = '/uploads/chat/' . $filename;
            $originalName = preg_replace('/[[:cntrl:]]/u', '', basename(str_replace('\\', '/', $file['name']))) ?: 'Attachment';
            $originalName = mb_strcut($originalName, 0, 200, 'UTF-8');
            
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
} catch (Throwable $e) {
    if (isset($filepath) && is_file($filepath)) {
        unlink($filepath);
    }
    error_log('Chat upload failed: ' . get_class($e));
    echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
}
?>