<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $tripId = $_GET['trip_id'];
    
    if (!$tripId) {
        echo json_encode(['success' => false, 'error' => 'Trip ID required']);
        exit;
    }
    
    // Get expense breakdown by category
    $stmt = $pdo->prepare("
        SELECT category, SUM(amount) as total 
        FROM expenses 
        WHERE trip_id = ? 
        GROUP BY category 
        ORDER BY total DESC
    ");
    $stmt->execute([$tripId]);
    $breakdown = $stmt->fetchAll();
    
    // Get subcategory breakdown
    $stmt = $pdo->prepare("
        SELECT category, subcategory, SUM(amount) as total 
        FROM expenses 
        WHERE trip_id = ? AND subcategory IS NOT NULL AND subcategory != ''
        GROUP BY category, subcategory 
        ORDER BY category, total DESC
    ");
    $stmt->execute([$tripId]);
    $subBreakdown = $stmt->fetchAll();
    
    $categories = [];
    $amounts = [];
    $subcategories = [];
    
    foreach ($breakdown as $item) {
        $categories[] = $item['category'];
        $amounts[] = floatval($item['total']);
    }
    
    // Group subcategories by category
    $subData = [];
    foreach ($subBreakdown as $item) {
        if (!isset($subData[$item['category']])) {
            $subData[$item['category']] = [];
        }
        $subData[$item['category']][] = [
            'name' => $item['subcategory'],
            'amount' => floatval($item['total'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'amounts' => $amounts,
        'subcategories' => $subData
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>