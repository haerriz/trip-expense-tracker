<?php
require_once '../includes/auth.php';
requireLogin();

$tripId = $_GET['trip_id'];
$userId = $_SESSION['user_id'];

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

// Simple HTML to PDF conversion
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="trip-expenses-' . $trip['name'] . '.pdf"');

// Basic PDF generation using HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .expense { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .total { font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($trip['name']) . '</h1>
        <p>Trip Report - Generated on ' . date('Y-m-d') . '</p>
    </div>
    
    <h2>Expenses</h2>';

$total = 0;
foreach ($expenses as $expense) {
    $total += $expense['amount'];
    $html .= '
    <div class="expense">
        <strong>' . htmlspecialchars($expense['category']) . '</strong> - ' . htmlspecialchars($expense['subcategory']) . '<br>
        ' . htmlspecialchars($expense['description']) . '<br>
        Amount: $' . number_format($expense['amount'], 2) . ' | Paid by: ' . htmlspecialchars($expense['paid_by_name']) . ' | Date: ' . $expense['date'] . '
    </div>';
}

$html .= '
    <div class="total">
        Total Expenses: $' . number_format($total, 2) . '
    </div>
</body>
</html>';

// Simple PDF generation using TCPDF-like approach
require_once '../config/simple-pdf.php';

try {
    $pdf = new SimplePDF();
    $pdf->addPage();
    $pdf->setFont('Arial', 'B', 16);
    $pdf->cell(0, 10, $trip['name'], 0, 1, 'C');
    $pdf->ln(5);
    
    $pdf->setFont('Arial', '', 12);
    $pdf->cell(0, 10, 'Trip Report - Generated on ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->ln(10);
    
    $pdf->setFont('Arial', 'B', 14);
    $pdf->cell(0, 10, 'Expenses', 0, 1);
    $pdf->ln(5);
    
    $pdf->setFont('Arial', '', 10);
    foreach ($expenses as $expense) {
        $pdf->cell(0, 8, $expense['category'] . ' - ' . $expense['subcategory'], 0, 1);
        $pdf->cell(0, 6, $expense['description'], 0, 1);
        $pdf->cell(0, 6, 'Amount: $' . number_format($expense['amount'], 2) . ' | Paid by: ' . $expense['paid_by_name'] . ' | Date: ' . $expense['date'], 0, 1);
        $pdf->ln(3);
    }
    
    $pdf->setFont('Arial', 'B', 12);
    $pdf->cell(0, 10, 'Total Expenses: $' . number_format($total, 2), 0, 1);
    
    $pdf->output('D', 'trip-expenses-' . $trip['name'] . '.pdf');
} catch (Exception $e) {
    // Fallback: output as formatted HTML
    header('Content-Type: text/html');
    echo '<style>body{font-family:Arial;margin:40px;} .expense{border-bottom:1px solid #ddd;padding:10px 0;} .total{font-weight:bold;margin-top:20px;}</style>';
    echo $html;
}
?>