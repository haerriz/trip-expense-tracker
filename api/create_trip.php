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
            
            // Add initial budget as expense record if budget is set
            if ($budget > 0) {
                // Ensure Budget category exists
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Budget'");
                $stmt->execute();
                if (!$stmt->fetch()) {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, subcategories) VALUES ('Budget', 'Initial Budget,Budget Increase,Budget Decrease')");
                    $stmt->execute();
                }
                
                // Create initial budget expense record
                $stmt = $pdo->prepare("
                    INSERT INTO expenses (trip_id, category, subcategory, amount, description, date, paid_by, created_at)
                    VALUES (?, 'Budget', 'Initial Budget', ?, 'Initial trip budget allocation', ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $tripId,
                    $budget,
                    $startDate ?: date('Y-m-d'),
                    $userId
                ]);
                
                $expenseId = $pdo->lastInsertId();
                
                // Add expense split for the creator
                $stmt = $pdo->prepare("
                    INSERT INTO expense_splits (expense_id, user_id, amount, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                
                $stmt->execute([$expenseId, $userId, $budget]);
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