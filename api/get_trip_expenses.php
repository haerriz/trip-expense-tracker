<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');


$tripId = $_GET['trip_id'];


// Get budget history (initial, increase, decrease)
$stmt = $pdo->prepare("
    SELECT bh.id, bh.change_type, bh.amount, bh.new_budget, bh.reason, bh.created_at, u.name as user_name
    FROM budget_history bh
    JOIN users u ON bh.user_id = u.id
    WHERE bh.trip_id = ?
    ORDER BY bh.created_at ASC
");
$stmt->execute([$tripId]);
$budgetHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent expenses
$stmt = $pdo->prepare("
    SELECT e.*, u.name as paid_by_name 
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

// Get total spent
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total_spent FROM expenses WHERE trip_id = ?");
$stmt->execute([$tripId]);
$totalSpent = floatval($stmt->fetchColumn());

echo json_encode([
    'expenses' => $expenses,
    'budget_history' => $budgetHistory,
    'budget' => $budget,
    'total_spent' => $totalSpent
]);
?>