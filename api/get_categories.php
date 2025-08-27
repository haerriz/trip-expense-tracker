<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Only show non-archived categories to users
    $stmt = $pdo->query("SELECT * FROM categories WHERE archived IS NULL OR archived = FALSE ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure subcategories field exists for each category
    foreach ($categories as &$category) {
        if (!isset($category['subcategories'])) {
            $category['subcategories'] = '';
        }
    }
    
    echo json_encode(['success' => true, 'categories' => $categories]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>