<?php
require_once '../includes/auth.php';
requireLogin();

$tripId = $_GET['trip_id'];

// Get trip data
$stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
$stmt->execute([$tripId]);
$trip = $stmt->fetch();

if (!$trip) {
    die('Trip not found');
}

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

// Calculate total
$total = 0;
foreach ($expenses as $expense) {
    $total += $expense['amount'];
}

// Set proper headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="trip-expenses-' . preg_replace('/[^a-zA-Z0-9]/', '-', $trip['name']) . '.pdf"');

// Generate simple PDF content
$content = "%PDF-1.4\n";
$content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
$content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
$content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";

// Build content stream
$text = "BT\n";
$text .= "/F1 16 Tf\n";
$text .= "50 750 Td\n";
$text .= "(" . $trip['name'] . ") Tj\n";
$text .= "0 -30 Td\n";
$text .= "/F1 12 Tf\n";
$text .= "(Trip Report - Generated on " . date('Y-m-d') . ") Tj\n";
$text .= "0 -40 Td\n";
$text .= "/F1 14 Tf\n";
$text .= "(Expenses) Tj\n";
$text .= "0 -25 Td\n";
$text .= "/F1 10 Tf\n";

$y = 655;
foreach ($expenses as $expense) {
    if ($y < 100) break; // Prevent overflow
    $text .= "0 " . ($y - 680) . " Td\n";
    $text .= "(" . $expense['category'] . " - " . $expense['subcategory'] . ") Tj\n";
    $y -= 15;
    $text .= "0 -15 Td\n";
    $text .= "(" . substr($expense['description'], 0, 50) . ") Tj\n";
    $y -= 15;
    $text .= "0 -15 Td\n";
    $text .= "($" . number_format($expense['amount'], 2) . " - " . $expense['paid_by_name'] . " - " . $expense['date'] . ") Tj\n";
    $y -= 20;
}

$text .= "0 -30 Td\n";
$text .= "/F1 12 Tf\n";
$text .= "(Total Expenses: $" . number_format($total, 2) . ") Tj\n";
$text .= "ET\n";

$content .= "4 0 obj\n<< /Length " . strlen($text) . " >>\nstream\n" . $text . "\nendstream\nendobj\n";
$content .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
$content .= "xref\n0 6\n0000000000 65535 f \n0000000010 00000 n \n0000000053 00000 n \n0000000125 00000 n \n0000000348 00000 n \n0000000565 00000 n \n";
$content .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . strlen($content) . "\n%%EOF\n";

echo $content;
?>