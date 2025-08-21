<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

// Debug logging
error_log('Add category - Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Add category - POST data: ' . print_r($_POST, true));
error_log('Add category - Session: ' . print_r($_SESSION, true));

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $subcategories = trim($_POST['subcategories'] ?? '');
        
        error_log('Add category - Name: ' . $name . ', Subcategories: ' . $subcategories);
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Category already exists']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, subcategories) VALUES (?, ?)");
        
        if ($stmt->execute([$name, $subcategories])) {
            echo json_encode(['success' => true, 'message' => 'Category added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add category']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>