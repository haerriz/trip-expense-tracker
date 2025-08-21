<?php
require_once '../includes/auth.php';
requireLogin();

$tripId = $_GET['trip_id'];

// Get trip data
$stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
$stmt->execute([$tripId]);
$trip = $stmt->fetch();

// Get expenses
$stmt = $pdo->prepare("
    SELECT e.*, u.name as paid_by_name 
    FROM expenses e 
    JOIN users u ON e.paid_by = u.id 
    WHERE e.trip_id = ? 
    ORDER BY e.date DESC
");
$stmt->execute([$tripId]);
$expenses = $stmt->fetchAll();

// Generate CSV (Excel compatible)
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="trip-expenses-' . $trip['name'] . '.csv"');

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, ['Trip Name', $trip['name']]);
fputcsv($output, ['Generated', date('Y-m-d H:i:s')]);
fputcsv($output, []);
fputcsv($output, ['Date', 'Category', 'Subcategory', 'Description', 'Amount', 'Paid By']);

// CSV Data
foreach ($expenses as $expense) {
    fputcsv($output, [
        $expense['date'],
        $expense['category'],
        $expense['subcategory'],
        $expense['description'],
        $expense['amount'],
        $expense['paid_by_name']
    ]);
}

// Total row
$total = array_sum(array_column($expenses, 'amount'));
fputcsv($output, []);
fputcsv($output, ['TOTAL', '', '', '', $total, '']);

fclose($output);
?>