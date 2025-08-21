<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $expenseId = $_POST['expense_id'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (empty($expenseId)) {
            echo json_encode(['success' => false, 'message' => 'Expense ID required']);
            exit;
        }
        
        // Verify user owns the expense or is master admin
        $stmt = $pdo->prepare("SELECT paid_by FROM expenses WHERE id = ?");
        $stmt->execute([$expenseId]);
        $expense = $stmt->fetch();
        
        if (!$expense) {
            echo json_encode(['success' => false, 'message' => 'Expense not found']);
            exit;
        }
        
        $isMasterAdmin = ($_SESSION['user_email'] === 'haerriz@gmail.com');
        $isOwner = ($expense['paid_by'] == $userId);
        
        if (!$isOwner && !$isMasterAdmin) {
            echo json_encode(['success' => false, 'message' => 'Only expense owner or master admin can delete expenses']);
            exit;
        }
        
        // Delete expense and related splits
        $pdo->beginTransaction();
        try {
            // Delete expense splits first
            $stmt = $pdo->prepare("DELETE FROM expense_splits WHERE expense_id = ?");
            $stmt->execute([$expenseId]);
            
            // Delete the expense
            $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
            $stmt->execute([$expenseId]);
            
            $pdo->commit();
            
            echo json_encode(['success' => true, 'message' => 'Expense deleted successfully']);
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to delete expense: ' . $e->getMessage()]);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>