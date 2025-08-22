<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $isTyping = $_POST['is_typing'] ?? false;
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId)) {
            echo json_encode(['success' => false, 'error' => 'Trip ID required']);
            exit;
        }
        
        // Store typing status in session or cache (Redis would be better for production)
        // For now, we'll use a simple file-based approach
        $typingFile = sys_get_temp_dir() . "/typing_{$tripId}.json";
        
        $typingData = [];
        if (file_exists($typingFile)) {
            $typingData = json_decode(file_get_contents($typingFile), true) ?: [];
        }
        
        // Clean old entries (older than 5 seconds)
        $currentTime = time();
        foreach ($typingData as $uid => $data) {
            if ($currentTime - $data['timestamp'] > 5) {
                unset($typingData[$uid]);
            }
        }
        
        if ($isTyping) {
            $typingData[$userId] = [
                'name' => $_SESSION['user_name'],
                'timestamp' => $currentTime
            ];
        } else {
            unset($typingData[$userId]);
        }
        
        file_put_contents($typingFile, json_encode($typingData));
        
        echo json_encode(['success' => true]);
    } else {
        // GET request - return current typing users
        $tripId = $_GET['trip_id'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId)) {
            echo json_encode(['success' => false, 'error' => 'Trip ID required']);
            exit;
        }
        
        $typingFile = sys_get_temp_dir() . "/typing_{$tripId}.json";
        $typingData = [];
        
        if (file_exists($typingFile)) {
            $typingData = json_decode(file_get_contents($typingFile), true) ?: [];
        }
        
        // Clean old entries and exclude current user
        $currentTime = time();
        $activeTypers = [];
        foreach ($typingData as $uid => $data) {
            if ($currentTime - $data['timestamp'] <= 5 && $uid != $userId) {
                $activeTypers[] = $data['name'];
            }
        }
        
        echo json_encode([
            'success' => true,
            'typing_users' => $activeTypers
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>