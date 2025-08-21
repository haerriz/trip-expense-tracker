<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC, created_at DESC LIMIT 20");
$stmt->execute([$userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['transactions' => $transactions]);
?>