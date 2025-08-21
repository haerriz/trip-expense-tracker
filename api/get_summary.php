<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'income'");
$stmt->execute([$userId]);
$income = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'expense'");
$stmt->execute([$userId]);
$expenses = $stmt->fetchColumn() ?: 0;

echo json_encode([
    'income' => $income,
    'expenses' => $expenses
]);
?>