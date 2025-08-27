<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $expenseId = $_GET['expense_id'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($expenseId)) {
            echo json_encode(['success' => false, 'message' => 'Expense ID required']);
            exit;
        }
        
        // Get expense details (only if user owns it)
        $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ? AND paid_by = ?");
        $stmt->execute([$expenseId, $userId]);
        $expense = $stmt->fetch();
        
        if (!$expense) {
            echo json_encode(['success' => false, 'message' => 'Expense not found or access denied']);
            exit;
        }
        
        echo json_encode(['success' => true, 'expense' => $expense]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $expenseId = $_POST['expense_id'] ?? '';
        $category = $_POST['category'] ?? '';
        $subcategory = $_POST['subcategory'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $date = $_POST['date'] ?? '';
        $paidBy = $_POST['paid_by'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($expenseId) || empty($category) || empty($description) || $amount <= 0 || empty($paidBy)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required and amount must be positive']);
            exit;
        }
        
        // Verify user owns the expense
        $stmt = $pdo->prepare("SELECT id FROM expenses WHERE id = ? AND paid_by = ?");
        $stmt->execute([$expenseId, $userId]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Expense not found or access denied']);
            exit;
        }
        
        // Update expense
        $stmt = $pdo->prepare("
            UPDATE expenses 
            SET category = ?, subcategory = ?, amount = ?, description = ?, date = ?, paid_by = ?
            WHERE id = ? AND paid_by = ?
        ");
        
        if ($stmt->execute([$category, $subcategory, $amount, $description, $date, $paidBy, $expenseId, $userId])) {
            echo json_encode(['success' => true, 'message' => 'Expense updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update expense']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>