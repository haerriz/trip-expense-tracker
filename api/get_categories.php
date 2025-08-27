<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Get categories, excluding archived ones if column exists
    try {
        // Check if archived column exists
        $pdo->query("SELECT archived FROM categories LIMIT 1");
        // Use archived column filter
        $stmt = $pdo->query("SELECT * FROM categories WHERE archived = 0 OR archived IS NULL ORDER BY name");
    } catch (PDOException $e) {
        // Archived column doesn't exist, get all categories
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    }
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