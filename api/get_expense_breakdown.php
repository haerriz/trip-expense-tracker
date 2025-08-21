<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$tripId = $_GET['trip_id'];

$stmt = $pdo->prepare("
    SELECT category, SUM(amount) as total 
    FROM expenses 
    WHERE trip_id = ? 
    GROUP BY category 
    ORDER BY total DESC
");
$stmt->execute([$tripId]);
$breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$amounts = [];

foreach ($breakdown as $item) {
    $categories[] = $item['category'];
    $amounts[] = floatval($item['total']);
}

echo json_encode([
    'categories' => $categories,
    'amounts' => $amounts
]);
?>