<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'adjust':
                adjustBudget();
                break;
            case 'set':
                setBudget();
                break;
            case 'history':
                getBudgetHistory();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        getBudgetHistory();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function adjustBudget() {
    global $pdo;
    
    $tripId = $_POST['trip_id'] ?? null;
    $adjustmentType = $_POST['adjustment_type'] ?? null; // 'increase' or 'decrease'
    $amount = floatval($_POST['amount'] ?? 0);
    $reason = $_POST['reason'] ?? '';
    
    if (!$tripId || !$adjustmentType || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    // Verify user has permission
    $stmt = $pdo->prepare("SELECT created_by, budget FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trip) {
        echo json_encode(['success' => false, 'message' => 'Trip not found']);
        return;
    }
    
    $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';
    $isCreator = $trip['created_by'] == $_SESSION['user_id'];
    
    if (!$isCreator && !$isMasterAdmin) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        return;
    }
    
    $currentBudget = floatval($trip['budget'] ?? 0);
    $newBudget = $adjustmentType === 'increase' ? $currentBudget + $amount : $currentBudget - $amount;
    
    if ($newBudget < 0) {
        echo json_encode(['success' => false, 'message' => 'Budget cannot be negative']);
        return;
    }
    
    $pdo->beginTransaction();
    try {
        // Record budget history
        $stmt = $pdo->prepare("INSERT INTO budget_history (trip_id, previous_budget, new_budget, adjustment_amount, adjustment_type, reason, adjusted_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tripId, $currentBudget, $newBudget, $amount, $adjustmentType, $reason, $_SESSION['user_id']]);
        
        // Update trip budget
        $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ?");
        $stmt->execute([$newBudget, $tripId]);
        
        // Create expense record for tracking
        $category = 'Budget Adjustment';
        $subcategory = $adjustmentType === 'increase' ? 'Budget Increase' : 'Budget Decrease';
        $description = $reason ?: ($adjustmentType === 'increase' ? "Budget increased by $amount" : "Budget decreased by $amount");
        
        $stmt = $pdo->prepare("INSERT INTO expenses (trip_id, category, subcategory, amount, description, date, paid_by, split_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'full')");
        $stmt->execute([$tripId, $category, $subcategory, $amount, $description, date('Y-m-d'), $_SESSION['user_id']]);
        
        $expenseId = $pdo->lastInsertId();
        
        // Add expense split for tracking
        $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
        $stmt->execute([$expenseId, $_SESSION['user_id'], $amount]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Budget {$adjustmentType}d by $amount successfully",
            'new_budget' => $newBudget,
            'previous_budget' => $currentBudget
        ]);
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

function setBudget() {
    global $pdo;
    
    $tripId = $_POST['trip_id'] ?? null;
    $newBudget = floatval($_POST['budget'] ?? 0);
    $reason = $_POST['reason'] ?? 'Budget set by user';
    
    if (!$tripId || $newBudget < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    // Verify user has permission
    $stmt = $pdo->prepare("SELECT created_by, budget FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trip) {
        echo json_encode(['success' => false, 'message' => 'Trip not found']);
        return;
    }
    
    $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';
    $isCreator = $trip['created_by'] == $_SESSION['user_id'];
    
    if (!$isCreator && !$isMasterAdmin) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        return;
    }
    
    $currentBudget = floatval($trip['budget'] ?? 0);
    
    $pdo->beginTransaction();
    try {
        // Record budget history
        $stmt = $pdo->prepare("INSERT INTO budget_history (trip_id, previous_budget, new_budget, adjustment_amount, adjustment_type, reason, adjusted_by) VALUES (?, ?, ?, ?, 'set', ?, ?)");
        $stmt->execute([$tripId, $currentBudget, $newBudget, abs($newBudget - $currentBudget), $reason, $_SESSION['user_id']]);
        
        // Update trip budget
        $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ?");
        $stmt->execute([$newBudget, $tripId]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Budget updated successfully',
            'new_budget' => $newBudget,
            'previous_budget' => $currentBudget
        ]);
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

function getBudgetHistory() {
    global $pdo;
    
    $tripId = $_GET['trip_id'] ?? $_POST['trip_id'] ?? null;
    
    if (!$tripId) {
        echo json_encode(['success' => false, 'message' => 'Trip ID required']);
        return;
    }
    
    // Verify user is member of the trip
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ?");
    $stmt->execute([$tripId, $_SESSION['user_id']]);
    
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        return;
    }
    
    // Get budget history
    $stmt = $pdo->prepare("
        SELECT bh.*, u.name as adjusted_by_name 
        FROM budget_history bh 
        JOIN users u ON bh.adjusted_by = u.id 
        WHERE bh.trip_id = ? 
        ORDER BY bh.created_at DESC
    ");
    $stmt->execute([$tripId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
}
?>