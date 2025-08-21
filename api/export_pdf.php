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

// Convert HTML to PDF using wkhtmltopdf if available, otherwise output HTML
if (shell_exec('which wkhtmltopdf')) {
    $tempFile = tempnam(sys_get_temp_dir(), 'trip_report');
    file_put_contents($tempFile . '.html', $html);
    shell_exec("wkhtmltopdf {$tempFile}.html {$tempFile}.pdf");
    readfile($tempFile . '.pdf');
    unlink($tempFile . '.html');
    unlink($tempFile . '.pdf');
} else {
    // Fallback: output as HTML with print styles
    header('Content-Type: text/html');
    echo $html;
}
?>