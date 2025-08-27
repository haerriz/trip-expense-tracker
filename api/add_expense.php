<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $category = $_POST['category'] ?? '';
        $subcategory = $_POST['subcategory'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $description = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? '';
        $splitType = $_POST['split_type'] ?? 'equal';
        $paidBy = $_POST['paid_by'] ?? $_SESSION['user_id'];
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId) || empty($category) || empty($amount)) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            exit;
        }
        
        // Verify user is member of the trip
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ? AND (status = 'accepted' OR status IS NULL)");
            $stmt->execute([$tripId, $userId]);
        } catch (Exception $e) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ?");
            $stmt->execute([$tripId, $userId]);
        }
        
        if ($stmt->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'message' => 'You are not a member of this trip']);
            exit;
        }
        
        // Validate amount
        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Amount must be greater than zero']);
            exit;
        }
        
        // Validate date
        if (!empty($date) && strtotime($date) > time()) {
            echo json_encode(['success' => false, 'message' => 'Expense date cannot be in the future']);
        }
        
        // Insert expense
        $stmt = $pdo->prepare("INSERT INTO expenses (trip_id, category, subcategory, amount, description, date, paid_by, split_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$tripId, $category, $subcategory, $amount, $description, $date, $paidBy, $splitType])) {
            $expenseId = $pdo->lastInsertId();
            
            // Handle splits
            if ($splitType === 'equal') {
                // Get trip members for equal split
                try {
                    $stmt = $pdo->prepare("SELECT user_id FROM trip_members WHERE trip_id = ? AND (status = 'accepted' OR status IS NULL)");
                    $stmt->execute([$tripId]);
                } catch (Exception $e) {
                    $stmt = $pdo->prepare("SELECT user_id FROM trip_members WHERE trip_id = ?");
                    $stmt->execute([$tripId]);
                }
                $members = $stmt->fetchAll();
                
                $splitAmount = $amount / count($members);
                
                foreach ($members as $member) {
                    $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
                    $stmt->execute([$expenseId, $member['user_id'], $splitAmount]);
                }
            } else if ($splitType === 'full') {
                // Full expense on the person who paid
                $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
                $stmt->execute([$expenseId, $paidBy, $amount]);
            } else if ($splitType === 'custom') {
                // Handle custom splits
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'split_') === 0 && $value > 0) {
                        $memberId = str_replace('split_', '', $key);
                        $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
                        $stmt->execute([$expenseId, $memberId, $value]);
                    }
                }
            }
            
            echo json_encode(['success' => true, 'expense_id' => $expenseId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add expense']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>