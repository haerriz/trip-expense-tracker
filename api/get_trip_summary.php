<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$tripId = $_GET['trip_id'];
$userId = $_SESSION['user_id'];

// Get trip budget and currency
$stmt = $pdo->prepare("SELECT budget, currency FROM trips WHERE id = ?");
$stmt->execute([$tripId]);
$trip = $stmt->fetch();
$budget = $trip['budget'] ?: 0;
$currency = $trip['currency'] ?: 'USD';

// Get total spent
$stmt = $pdo->prepare("SELECT SUM(amount) FROM expenses WHERE trip_id = ?");
$stmt->execute([$tripId]);
$totalSpent = $stmt->fetchColumn() ?: 0;

// Get user's share
$stmt = $pdo->prepare("SELECT SUM(amount) FROM expense_splits WHERE user_id = ? AND expense_id IN (SELECT id FROM expenses WHERE trip_id = ?)");
$stmt->execute([$userId, $tripId]);
$myShare = $stmt->fetchColumn() ?: 0;

echo json_encode([
    'budget' => $budget,
    'total_spent' => $totalSpent,
    'my_share' => $myShare,
    'currency' => $currency
]);
?>