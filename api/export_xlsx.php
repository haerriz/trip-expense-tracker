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

// Get expense breakdown for summary
$stmt = $pdo->prepare("
    SELECT category, SUM(amount) as total_amount 
    FROM expenses 
    WHERE trip_id = ? 
    GROUP BY category 
    ORDER BY total_amount DESC
");
$stmt->execute([$tripId]);
$breakdown = $stmt->fetchAll();

$filename = 'trip-expenses-' . preg_replace('/[^a-zA-Z0-9-_]/', '', $trip['name']) . '.xlsx';

// Change to CSV format that Excel can open as XLSX
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . str_replace('.xlsx', '.csv', $filename) . '"');
header('Cache-Control: max-age=0');

// Create CSV content that Excel can open properly
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Trip header
fputcsv($output, ['Trip Expense Report']);
fputcsv($output, ['Trip Name:', $trip['name']]);
fputcsv($output, ['Budget:', '$' . number_format($trip['budget'] ?? 0, 2)]);
fputcsv($output, ['Currency:', $trip['currency'] ?? 'USD']);
fputcsv($output, ['Export Date:', date('Y-m-d H:i:s')]);
fputcsv($output, []);

// Expense breakdown summary
fputcsv($output, ['EXPENSE BREAKDOWN BY CATEGORY']);
fputcsv($output, ['Category', 'Total Amount', 'Percentage']);
$totalExpenses = array_sum(array_column($breakdown, 'total_amount'));
foreach ($breakdown as $item) {
    $percentage = $totalExpenses > 0 ? round(($item['total_amount'] / $totalExpenses) * 100, 1) : 0;
    fputcsv($output, [$item['category'], '$' . number_format($item['total_amount'], 2), $percentage . '%']);
}
fputcsv($output, ['TOTAL', '$' . number_format($totalExpenses, 2), '100%']);
fputcsv($output, []);

// Detailed expenses
fputcsv($output, ['DETAILED EXPENSES']);
fputcsv($output, ['Date', 'Category', 'Subcategory', 'Description', 'Amount', 'Paid By']);

foreach ($expenses as $expense) {
    fputcsv($output, [
        $expense['date'],
        $expense['category'],
        $expense['subcategory'],
        $expense['description'],
        '$' . number_format($expense['amount'], 2),
        $expense['paid_by_name']
    ]);
}

// Summary totals
fputcsv($output, []);
fputcsv($output, ['SUMMARY']);
fputcsv($output, ['Total Expenses:', '$' . number_format($totalExpenses, 2)]);
fputcsv($output, ['Number of Expenses:', count($expenses)]);
if ($trip['budget']) {
    $remaining = $trip['budget'] - $totalExpenses;
    fputcsv($output, ['Remaining Budget:', '$' . number_format($remaining, 2)]);
    fputcsv($output, ['Budget Usage:', round(($totalExpenses / $trip['budget']) * 100, 1) . '%']);
}

fclose($output);
?>