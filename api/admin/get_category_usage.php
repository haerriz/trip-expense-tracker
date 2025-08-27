<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

try {
    // Get category usage statistics
    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.name,
            c.subcategories,
            c.archived,
            COUNT(e.id) as expense_count,
            COALESCE(SUM(e.amount), 0) as total_amount,
            COUNT(DISTINCT e.trip_id) as trip_count
        FROM categories c
        LEFT JOIN expenses e ON c.name = e.category
        GROUP BY c.id, c.name, c.subcategories, c.archived
        ORDER BY expense_count DESC, c.name
    ");
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get subcategory usage for each category
    foreach ($categories as &$category) {
        if ($category['subcategories']) {
            $subcats = array_filter(array_map('trim', explode(',', $category['subcategories'])));
            $subcatUsage = [];
            
            foreach ($subcats as $subcat) {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
                    FROM expenses 
                    WHERE category = ? AND subcategory = ?
                ");
                $stmt->execute([$category['name'], $subcat]);
                $usage = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $subcatUsage[] = [
                    'name' => $subcat,
                    'expense_count' => (int)$usage['count'],
                    'total_amount' => (float)$usage['total']
                ];
            }
            
            $category['subcategory_usage'] = $subcatUsage;
        } else {
            $category['subcategory_usage'] = [];
        }
    }
    
    echo json_encode([
        'success' => true, 
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>