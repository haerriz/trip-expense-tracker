<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $noBudget = $_POST['no_budget'] ?? false;
        $budget = ($noBudget === 'true' || $noBudget === true) ? null : floatval($_POST['budget'] ?? 0);
        $currency = $_POST['currency'] ?? 'USD';
        $userId = $_SESSION['user_id'];
        
        // Validation
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Trip name is required']);
            exit;
        }
        
        if ($budget !== null && $budget < 0) {
            echo json_encode(['success' => false, 'message' => 'Budget cannot be negative']);
            exit;
        }
        
        if (!empty($startDate) && !empty($endDate) && strtotime($startDate) > strtotime($endDate)) {
            echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
            exit;
        }
        
        // Create trip
        $stmt = $pdo->prepare("INSERT INTO trips (name, description, start_date, end_date, budget, currency, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $description, $startDate ?: null, $endDate ?: null, $budget, $currency, $userId])) {
            $tripId = $pdo->lastInsertId();
            
            // Add creator as accepted member
            try {
                $stmt = $pdo->prepare("INSERT INTO trip_members (trip_id, user_id, status, joined_at) VALUES (?, ?, 'accepted', NOW())");
                $stmt->execute([$tripId, $userId]);
            } catch (Exception $e) {
                // Fallback for old schema
                $stmt = $pdo->prepare("INSERT INTO trip_members (trip_id, user_id) VALUES (?, ?)");
                $stmt->execute([$tripId, $userId]);
            }
            
            echo json_encode(['success' => true, 'trip_id' => $tripId, 'message' => 'Trip created successfully']);
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