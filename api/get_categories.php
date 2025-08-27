<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Get all categories (archived column may not exist on all servers)
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure subcategories field exists for each category
    foreach ($categories as &$category) {
        if (!isset($category['subcategories'])) {
            $category['subcategories'] = '';
        }
    }
    
    echo json_encode([
        'success' => true, 
        'categories' => $categories,
        'count' => count($categories),
        'debug' => 'Categories loaded successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'debug' => 'Exception occurred'
    ]);
}
?>