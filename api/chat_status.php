<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$tripId = $_GET['trip_id'] ?? $_POST['trip_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$tripId) {
    echo json_encode(['success' => false, 'error' => 'No trip ID provided']);
    exit;
}

// Create tables if they don't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS trip_typing (
        trip_id INT,
        user_id INT,
        last_typing TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (trip_id, user_id)
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS trip_online (
        trip_id INT,
        user_id INT,
        last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (trip_id, user_id)
    )");
} catch (Exception $e) {
    // Tables might already exist, continue
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'typing') {
        // Update typing status
        try {
            $stmt = $pdo->prepare("
                INSERT INTO trip_typing (trip_id, user_id, last_typing) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE last_typing = NOW()
            ");
            $stmt->execute([$tripId, $userId]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
    } elseif ($action === 'stop_typing') {
        // Remove typing status
        $stmt = $pdo->prepare("DELETE FROM trip_typing WHERE trip_id = ? AND user_id = ?");
        $stmt->execute([$tripId, $userId]);
        echo json_encode(['success' => true]);
        
    } elseif ($action === 'heartbeat') {
        // Update online status
        try {
            $stmt = $pdo->prepare("
                INSERT INTO trip_online (trip_id, user_id, last_seen) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE last_seen = NOW()
            ");
            $stmt->execute([$tripId, $userId]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
} else {
    // Get typing status
    $stmt = $pdo->prepare("
        SELECT u.name 
        FROM trip_typing tt 
        JOIN users u ON tt.user_id = u.id 
        WHERE tt.trip_id = ? AND tt.user_id != ? 
        AND tt.last_typing > DATE_SUB(NOW(), INTERVAL 3 SECOND)
    ");
    $stmt->execute([$tripId, $userId]);
    $typing = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get online status
    $stmt = $pdo->prepare("
        SELECT u.name 
        FROM trip_online tol 
        JOIN users u ON tol.user_id = u.id 
        WHERE tol.trip_id = ? 
        AND tol.last_seen > DATE_SUB(NOW(), INTERVAL 30 SECOND)
    ");
    $stmt->execute([$tripId]);
    $online = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'typing' => $typing,
        'online' => $online
    ]);
}
?>