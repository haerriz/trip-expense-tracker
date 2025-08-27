<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $categoryId = $_GET['category_id'] ?? '';
        
        if (empty($categoryId)) {
            echo json_encode(['success' => false, 'message' => 'Category ID required']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch();
        
        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            exit;
        }
        
        echo json_encode(['success' => true, 'category' => $category]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $categoryId = $_POST['category_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $subcategories = trim($_POST['subcategories'] ?? '');
        
        if (empty($categoryId) || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category ID and name are required']);
            exit;
        }
        
        // Check if another category with this name exists (excluding current)
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $stmt->execute([$name, $categoryId]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Category name already exists']);
            exit;
        }
        
        // Get current category data
        $stmt = $pdo->prepare("SELECT name, subcategories FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $currentCategory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if name is changing and if old name is used in expenses
        if ($currentCategory['name'] !== $name) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM expenses WHERE category = ?");
            $stmt->execute([$currentCategory['name']]);
            $expenseCount = $stmt->fetchColumn();
            
            if ($expenseCount > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Cannot rename category '{$currentCategory['name']}' - it has {$expenseCount} existing expenses. Create a new category instead."
                ]);
                exit;
            }
        }
        
        // Check if subcategories are being removed that are used in expenses
        $oldSubcats = array_filter(array_map('trim', explode(',', $currentCategory['subcategories'] ?? '')));
        $newSubcats = array_filter(array_map('trim', explode(',', $subcategories)));
        $removedSubcats = array_diff($oldSubcats, $newSubcats);
        
        if (!empty($removedSubcats)) {
            foreach ($removedSubcats as $removedSubcat) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM expenses WHERE category = ? AND subcategory = ?");
                $stmt->execute([$currentCategory['name'], $removedSubcat]);
                $subExpenseCount = $stmt->fetchColumn();
                
                if ($subExpenseCount > 0) {
                    echo json_encode([
                        'success' => false, 
                        'message' => "Cannot remove subcategory '{$removedSubcat}' - it has {$subExpenseCount} existing expenses."
                    ]);
                    exit;
                }
            }
        }
        
        // Update category
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, subcategories = ? WHERE id = ?");
        
        if ($stmt->execute([$name, $subcategories, $categoryId])) {
            echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update category']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>