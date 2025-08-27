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
    
    // Get budget history from expenses table
    $stmt = $pdo->prepare("
        SELECT e.id, e.subcategory as adjustment_type, e.amount as adjustment_amount, 
               e.description as reason, e.created_at, u.name as user_name
        FROM expenses e
        JOIN users u ON e.paid_by = u.id
        WHERE e.trip_id = ? AND e.category = 'Budget'
        ORDER BY e.created_at ASC
    ");
    $stmt->execute([$tripId]);
    $budgetHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent expenses
    $stmt = $pdo->prepare("
        SELECT e.*, u.name as paid_by_name, u.picture as paid_by_picture
        FROM expenses e 
        JOIN users u ON e.paid_by = u.id 
        WHERE e.trip_id = ? 
        ORDER BY e.date DESC, e.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$tripId]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get trip budget
    $stmt = $pdo->prepare("SELECT budget FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    $budget = isset($trip['budget']) ? floatval($trip['budget']) : null;
    
    // Get total spent (excluding budget category)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total_spent FROM expenses WHERE trip_id = ? AND category != 'Budget'");
    $stmt->execute([$tripId]);
    $totalSpent = floatval($stmt->fetchColumn());
    
    echo json_encode([
        'success' => true,
        'expenses' => $expenses,
        'budget_history' => $budgetHistory,
        'budget' => $budget,
        'total_spent' => $totalSpent
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>