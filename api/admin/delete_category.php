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
        
        // Get category name first
        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $categoryName = $stmt->fetchColumn();
        
        if (!$categoryName) {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            exit;
        }
        
        // Check if category is being used in expenses
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM expenses WHERE category = ?");
        $stmt->execute([$categoryName]);
        $expenseCount = $stmt->fetchColumn();
        
        if ($expenseCount > 0) {
            echo json_encode([
                'success' => false, 
                'message' => "Cannot delete category '{$categoryName}' - it has {$expenseCount} existing expenses. Archive it instead or reassign expenses first."
            ]);
            exit;
        }
        
        // Soft delete by marking as archived instead of hard delete
        $stmt = $pdo->prepare("UPDATE categories SET name = CONCAT('[ARCHIVED] ', name), archived = 1 WHERE id = ?");
        
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