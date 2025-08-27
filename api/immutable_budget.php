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
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function adjustBudget() {
    global $pdo;
    
    $tripId = $_POST['trip_id'] ?? null;
    $adjustmentType = $_POST['adjustment_type'] ?? null;
    $amount = floatval($_POST['amount'] ?? 0);
    
    if (!$tripId || !$adjustmentType || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    // Get current budget
    $stmt = $pdo->prepare("SELECT created_by, budget FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trip) {
        echo json_encode(['success' => false, 'message' => 'Trip not found']);
        return;
    }
    
    // Check permission
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
    
    // Update budget
    $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ?");
    $stmt->execute([$newBudget, $tripId]);

    // Log adjustment in budget_history
    $userId = $_SESSION['user_id'];
    $reason = $_POST['reason'] ?? ("Budget {$adjustmentType}d");
    $stmt = $pdo->prepare("INSERT INTO budget_history (trip_id, user_id, change_type, amount, new_budget, reason) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$tripId, $userId, $adjustmentType, $amount, $newBudget, $reason]);

    echo json_encode([
        'success' => true,
        'message' => "Budget {$adjustmentType}d successfully",
        'new_budget' => $newBudget
    ]);
}

function setBudget() {
    global $pdo;
    
    $tripId = $_POST['trip_id'] ?? null;
    $newBudget = floatval($_POST['budget'] ?? 0);
    
    if (!$tripId || $newBudget < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    // Get trip info
    $stmt = $pdo->prepare("SELECT created_by FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trip) {
        echo json_encode(['success' => false, 'message' => 'Trip not found']);
        return;
    }
    
    // Check permission
    $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';
    $isCreator = $trip['created_by'] == $_SESSION['user_id'];
    
    if (!$isCreator && !$isMasterAdmin) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        return;
    }
    
    // Update budget
    $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ?");
    $stmt->execute([$newBudget, $tripId]);

    // Log initial budget if not already present
    $userId = $_SESSION['user_id'];
    $reason = $_POST['reason'] ?? 'Initial budget set';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM budget_history WHERE trip_id = ? AND change_type = 'initial'");
    $stmt->execute([$tripId]);
    $hasInitial = $stmt->fetchColumn();
    if (!$hasInitial && $newBudget > 0) {
        $stmt = $pdo->prepare("INSERT INTO budget_history (trip_id, user_id, change_type, amount, new_budget, reason) VALUES (?, ?, 'initial', ?, ?, ?)");
        $stmt->execute([$tripId, $userId, $newBudget, $newBudget, $reason]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Budget updated successfully',
        'new_budget' => $newBudget
    ]);
}
?>