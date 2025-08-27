<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');


$tripId = $_GET['trip_id'];

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
    'budget' => $budget,
    'total_spent' => $totalSpent
]);
?>