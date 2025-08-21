<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $categoryId = $_POST['category_id'] ?? '';
        
        if (empty($categoryId)) {
            echo json_encode(['success' => false, 'message' => 'Category ID required']);
            exit;
        }
        
        // Check if category is being used in expenses
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM expenses WHERE category = (SELECT name FROM categories WHERE id = ?)");
        $stmt->execute([$categoryId]);
        $expenseCount = $stmt->fetchColumn();
        
        if ($expenseCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete category with existing expenses']);
            exit;
        }
        
        // Delete category
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        
        if ($stmt->execute([$categoryId])) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>