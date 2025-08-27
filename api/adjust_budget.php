<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit;
    }
    
    $tripId = $_POST['trip_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'increase' or 'decrease'
    $amount = floatval($_POST['amount'] ?? 0);
    
    if (!$tripId || !$action || $amount <= 0) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        exit;
    }
    
    // Verify user has permission (trip creator or master admin)
    $stmt = $pdo->prepare("SELECT created_by, budget FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trip) {
        echo json_encode(['success' => false, 'error' => 'Trip not found']);
        exit;
    }
    
    $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';
    $isCreator = $trip['created_by'] == $_SESSION['user_id'];
    
    if (!$isCreator && !$isMasterAdmin) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }
    
    // Calculate new budget
    $currentBudget = floatval($trip['budget'] ?? 0);
    $newBudget = $action === 'increase' ? $currentBudget + $amount : $currentBudget - $amount;
    
    if ($newBudget < 0) {
        echo json_encode(['success' => false, 'error' => 'Budget cannot be negative']);
        exit;
    }
    
    // Update trip budget
    $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ?");
    $stmt->execute([$newBudget, $tripId]);
    
    // Ensure Budget category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Budget'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, subcategories) VALUES ('Budget', 'Initial Budget,Budget Increase,Budget Decrease')");
        $stmt->execute();
    }
    
    // Create budget adjustment expense record
    $subcategory = $action === 'increase' ? 'Budget Increase' : 'Budget Decrease';
    $description = $action === 'increase' ? 
        "Budget increased by $amount" : 
        "Budget decreased by $amount";
    
    $stmt = $pdo->prepare("
        INSERT INTO expenses (trip_id, category, subcategory, amount, description, date, paid_by, created_at)
        VALUES (?, 'Budget', ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $tripId,
        $subcategory,
        $amount,
        $description,
        date('Y-m-d'),
        $_SESSION['user_id']
    ]);
    
    $expenseId = $pdo->lastInsertId();
    
    // Add expense split
    $stmt = $pdo->prepare("
        INSERT INTO expense_splits (expense_id, user_id, amount)
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([$expenseId, $_SESSION['user_id'], $amount]);
    
    echo json_encode([
        'success' => true,
        'message' => "Budget {$action}d by $amount successfully",
        'new_budget' => $newBudget
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>