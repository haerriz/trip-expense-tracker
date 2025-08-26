<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Ensure Budget category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Budget'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, subcategories) VALUES ('Budget', 'Initial Budget,Budget Increase,Budget Decrease')");
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Budget category created']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Budget category already exists']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>