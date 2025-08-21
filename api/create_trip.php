<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../includes/auth.php';
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $budget = $_POST['budget'];
        $currency = $_POST['currency'];
        $userId = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("INSERT INTO trips (name, description, start_date, end_date, budget, currency, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $description, $startDate, $endDate, $budget, $currency, $userId])) {
            $tripId = $pdo->lastInsertId();
            
            // Add creator as member
            $stmt = $pdo->prepare("INSERT INTO trip_members (trip_id, user_id) VALUES (?, ?)");
            $stmt->execute([$tripId, $userId]);
            
            echo json_encode(['success' => true, 'trip_id' => $tripId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>