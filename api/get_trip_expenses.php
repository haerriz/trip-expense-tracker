<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$tripId = $_GET['trip_id'];

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

echo json_encode(['expenses' => $expenses]);
?>