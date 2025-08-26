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
    
    // Get all expenses
    $stmt = $pdo->prepare("
        SELECT e.*, u.name as paid_by_name 
        FROM expenses e 
        JOIN users u ON e.paid_by = u.id 
        WHERE e.trip_id = ?
        ORDER BY e.date DESC, e.created_at DESC
    ");
    $stmt->execute([$tripId]);
    $expenses = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'expenses' => $expenses
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>