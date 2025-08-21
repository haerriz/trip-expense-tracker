<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $budget = $_POST['budget'] ?? 0;
        $currency = $_POST['currency'] ?? 'USD';
        $userId = $_SESSION['user_id'];
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Trip name is required']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO trips (name, description, start_date, end_date, budget, currency, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $description, $startDate, $endDate, $budget, $currency, $userId])) {
            $tripId = $pdo->lastInsertId();
            
            // Add creator as member
            $stmt = $pdo->prepare("INSERT INTO trip_members (trip_id, user_id) VALUES (?, ?)");
            $stmt->execute([$tripId, $userId]);
            
            echo json_encode(['success' => true, 'trip_id' => $tripId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create trip']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>