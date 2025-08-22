<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $tripId = $_GET['trip_id'] ?? $_POST['trip_id'] ?? '';
    $userId = $_SESSION['user_id'];
    
    if (!$tripId) {
        echo json_encode(['success' => false, 'error' => 'Trip ID required']);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update user's online status
        $action = $_POST['action'] ?? 'heartbeat';
        
        // Use a simple file-based approach for online status
        $onlineFile = sys_get_temp_dir() . "/online_{$tripId}.json";
        
        $onlineData = [];
        if (file_exists($onlineFile)) {
            $onlineData = json_decode(file_get_contents($onlineFile), true) ?: [];
        }
        
        // Clean old entries (older than 30 seconds)
        $currentTime = time();
        foreach ($onlineData as $uid => $data) {
            if ($currentTime - $data['timestamp'] > 30) {
                unset($onlineData[$uid]);
            }
        }
        
        // Update current user's status
        $onlineData[$userId] = [
            'name' => $_SESSION['user_name'],
            'timestamp' => $currentTime
        ];
        
        file_put_contents($onlineFile, json_encode($onlineData));
        
        echo json_encode(['success' => true]);
        
    } else {
        // GET request - return online users
        $onlineFile = sys_get_temp_dir() . "/online_{$tripId}.json";
        $onlineData = [];
        
        if (file_exists($onlineFile)) {
            $onlineData = json_decode(file_get_contents($onlineFile), true) ?: [];
        }
        
        // Clean old entries and get active users
        $currentTime = time();
        $activeUsers = [];
        foreach ($onlineData as $uid => $data) {
            if ($currentTime - $data['timestamp'] <= 30) {
                $activeUsers[] = [
                    'id' => $uid,
                    'name' => $data['name'],
                    'is_current' => $uid == $userId
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'online_users' => $activeUsers,
            'count' => count($activeUsers)
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>