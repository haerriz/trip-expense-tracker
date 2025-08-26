<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add':
                addExpense();
                break;
            case 'modify':
                modifyExpense();
                break;
            case 'deactivate':
                deactivateExpense();
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

function addExpense() {
    global $pdo;
    
    $tripId = $_POST['trip_id'] ?? '';
    $category = $_POST['category'] ?? '';
    $subcategory = $_POST['subcategory'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $splitType = $_POST['split_type'] ?? 'equal';
    $userId = $_SESSION['user_id'];
    
    if (empty($tripId) || empty($category) || empty($amount)) {
        echo json_encode(['success' => false, 'message' => 'Required fields missing']);
        return;
    }
    
    // Verify user is member of the trip
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ?");
    $stmt->execute([$tripId, $userId]);
    
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'You are not a member of this trip']);
        return;
    }
    
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than zero']);
        return;
    }
    
    $pdo->beginTransaction();
    try {
        // Insert expense
        $stmt = $pdo->prepare("INSERT INTO expenses (trip_id, category, subcategory, amount, description, date, paid_by, split_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$tripId, $category, $subcategory, $amount, $description, $date, $userId, $splitType])) {
            $expenseId = $pdo->lastInsertId();
            
            // Handle splits
            handleExpenseSplits($expenseId, $tripId, $amount, $splitType, $userId);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'expense_id' => $expenseId]);
        } else {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to add expense']);
        }
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

function modifyExpense() {
    global $pdo;
    
    $expenseId = $_POST['expense_id'] ?? '';
    $category = $_POST['category'] ?? '';
    $subcategory = $_POST['subcategory'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $reason = $_POST['reason'] ?? 'Modified by user';
    $userId = $_SESSION['user_id'];
    
    if (empty($expenseId) || empty($category) || empty($amount)) {
        echo json_encode(['success' => false, 'message' => 'Required fields missing']);
        return;
    }
    
    // Get original expense
    $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->execute([$expenseId]);
    $originalExpense = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$originalExpense) {
        echo json_encode(['success' => false, 'message' => 'Expense not found']);
        return;
    }
    
    // Verify ownership
    if ($originalExpense['paid_by'] != $userId && $_SESSION['user_email'] !== 'haerriz@gmail.com') {
        echo json_encode(['success' => false, 'message' => 'Only expense owner can modify expenses']);
        return;
    }
    
    $pdo->beginTransaction();
    try {
        // Update expense directly
        $stmt = $pdo->prepare("UPDATE expenses SET category = ?, subcategory = ?, amount = ?, description = ?, date = ? WHERE id = ?");
        $stmt->execute([$category, $subcategory, $amount, $description, $date, $expenseId]);
        
        // Delete old splits
        $stmt = $pdo->prepare("DELETE FROM expense_splits WHERE expense_id = ?");
        $stmt->execute([$expenseId]);
        
        // Handle new splits
        handleExpenseSplits($expenseId, $originalExpense['trip_id'], $amount, $originalExpense['split_type'], $originalExpense['paid_by']);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Expense updated successfully']);
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

function deactivateExpense() {
    global $pdo;
    
    $expenseId = $_POST['expense_id'] ?? '';
    $reason = $_POST['reason'] ?? 'Deleted by user';
    $userId = $_SESSION['user_id'];
    
    if (empty($expenseId)) {
        echo json_encode(['success' => false, 'message' => 'Expense ID required']);
        return;
    }
    
    // Get expense
    $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->execute([$expenseId]);
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$expense) {
        echo json_encode(['success' => false, 'message' => 'Expense not found']);
        return;
    }
    
    // Verify ownership
    if ($expense['paid_by'] != $userId && $_SESSION['user_email'] !== 'haerriz@gmail.com') {
        echo json_encode(['success' => false, 'message' => 'Only expense owner can delete expenses']);
        return;
    }
    
    $pdo->beginTransaction();
    try {
        // Delete expense
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->execute([$expenseId]);
        
        // Remove splits
        $stmt = $pdo->prepare("DELETE FROM expense_splits WHERE expense_id = ?");
        $stmt->execute([$expenseId]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Expense deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

function handleExpenseSplits($expenseId, $tripId, $amount, $splitType, $paidBy) {
    global $pdo;
    
    if ($splitType === 'equal') {
        $stmt = $pdo->prepare("SELECT user_id FROM trip_members WHERE trip_id = ?");
        $stmt->execute([$tripId]);
        $members = $stmt->fetchAll();
        
        $splitAmount = $amount / count($members);
        
        foreach ($members as $member) {
            $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
            $stmt->execute([$expenseId, $member['user_id'], $splitAmount]);
        }
    } else if ($splitType === 'full') {
        $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
        $stmt->execute([$expenseId, $paidBy, $amount]);
    } else if ($splitType === 'custom') {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'split_') === 0 && $value > 0) {
                $memberId = str_replace('split_', '', $key);
                $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
                $stmt->execute([$expenseId, $memberId, $value]);
            }
        }
    }
}
?>