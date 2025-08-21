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

// Get expense breakdown for analysis
$stmt = $pdo->prepare("
    SELECT category, SUM(amount) as total_amount, COUNT(*) as expense_count
    FROM expenses 
    WHERE trip_id = ? 
    GROUP BY category 
    ORDER BY total_amount DESC
");
$stmt->execute([$tripId]);
$breakdown = $stmt->fetchAll();

// Get trip members
$stmt = $pdo->prepare("
    SELECT u.name, COALESCE(SUM(e.amount), 0) as total_paid
    FROM trip_members tm
    JOIN users u ON tm.user_id = u.id
    LEFT JOIN expenses e ON e.paid_by = u.id AND e.trip_id = ?
    WHERE tm.trip_id = ?
    GROUP BY u.id, u.name
    ORDER BY total_paid DESC
");
$stmt->execute([$tripId, $tripId]);
$members = $stmt->fetchAll();

$total = array_sum(array_column($expenses, 'amount'));
$currency = $trip['currency'] ?? 'USD';
$currencySymbol = ['USD' => '$', 'EUR' => '€', 'GBP' => '£'][$currency] ?? '$';

// Use HTML to PDF conversion for better formatting
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Trip Expense Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .summary { background: #f5f5f5; padding: 15px; margin: 20px 0; }
        .chart-section { margin: 20px 0; }
        .breakdown-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .breakdown-table th, .breakdown-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .breakdown-table th { background-color: #f2f2f2; }
        .expense-item { margin: 10px 0; padding: 10px; border-bottom: 1px solid #eee; }
        .total { font-weight: bold; font-size: 18px; color: #2196F3; }
        .chart-bar { height: 20px; background: #2196F3; margin: 2px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($trip['name']) ?></h1>
        <h3>Trip Expense Report</h3>
        <p>Generated on <?= date('F j, Y') ?></p>
    </div>

    <div class="summary">
        <h3>Trip Summary</h3>
        <p><strong>Budget:</strong> <?= $trip['budget'] ? $currencySymbol . number_format($trip['budget'], 2) : 'No Budget Set' ?></p>
        <p><strong>Total Expenses:</strong> <span class="total"><?= $currencySymbol . number_format($total, 2) ?></span></p>
        <p><strong>Number of Expenses:</strong> <?= count($expenses) ?></p>
        <?php if ($trip['budget']): ?>
            <p><strong>Remaining Budget:</strong> <?= $currencySymbol . number_format($trip['budget'] - $total, 2) ?></p>
            <p><strong>Budget Usage:</strong> <?= round(($total / $trip['budget']) * 100, 1) ?>%</p>
        <?php endif; ?>
    </div>

    <div class="chart-section">
        <h3>Expense Analysis by Category</h3>
        <table class="breakdown-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Percentage</th>
                    <th>Count</th>
                    <th>Visual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($breakdown as $item): ?>
                    <?php $percentage = $total > 0 ? ($item['total_amount'] / $total) * 100 : 0; ?>
                    <tr>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td><?= $currencySymbol . number_format($item['total_amount'], 2) ?></td>
                        <td><?= round($percentage, 1) ?>%</td>
                        <td><?= $item['expense_count'] ?></td>
                        <td>
                            <div class="chart-bar" style="width: <?= $percentage ?>%; max-width: 200px;"></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="chart-section">
        <h3>Expenses by Member</h3>
        <table class="breakdown-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Total Paid</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <?php $percentage = $total > 0 ? ($member['total_paid'] / $total) * 100 : 0; ?>
                    <tr>
                        <td><?= htmlspecialchars($member['name']) ?></td>
                        <td><?= $currencySymbol . number_format($member['total_paid'], 2) ?></td>
                        <td><?= round($percentage, 1) ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h3>Detailed Expenses</h3>
    <?php foreach ($expenses as $expense): ?>
        <div class="expense-item">
            <strong><?= htmlspecialchars($expense['category']) ?></strong>
            <?php if ($expense['subcategory']): ?>
                - <?= htmlspecialchars($expense['subcategory']) ?>
            <?php endif; ?>
            <br>
            <em><?= htmlspecialchars($expense['description']) ?></em><br>
            <strong><?= $currencySymbol . number_format($expense['amount'], 2) ?></strong> 
            paid by <?= htmlspecialchars($expense['paid_by_name']) ?> 
            on <?= date('M j, Y', strtotime($expense['date'])) ?>
        </div>
    <?php endforeach; ?>

    <div class="summary">
        <h3>Report Summary</h3>
        <p class="total">Total Trip Expenses: <?= $currencySymbol . number_format($total, 2) ?></p>
        <p>Report generated on <?= date('F j, Y \a\t g:i A') ?></p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Generate proper PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="trip-expenses-' . preg_replace('/[^a-zA-Z0-9]/', '-', $trip['name']) . '.pdf"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Simple PDF generation
$pdf_content = generateSimplePDF($trip, $expenses, $breakdown, $members, $total, $currencySymbol);
echo $pdf_content;

function generateSimplePDF($trip, $expenses, $breakdown, $members, $total, $currencySymbol) {
    $content = "%PDF-1.4\n";
    $content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>\nendobj\n";
    
    // Build content stream
    $text = "BT\n";
    $text .= "/F2 20 Tf\n";
    $text .= "50 750 Td\n";
    $text .= "(" . substr($trip['name'], 0, 40) . ") Tj\n";
    $text .= "0 -25 Td\n";
    $text .= "/F1 12 Tf\n";
    $text .= "(Trip Expense Report - " . date('M j, Y') . ") Tj\n";
    
    // Summary section
    $text .= "0 -30 Td\n";
    $text .= "/F2 14 Tf\n";
    $text .= "(SUMMARY) Tj\n";
    $text .= "0 -20 Td\n";
    $text .= "/F1 11 Tf\n";
    $text .= "(Total Expenses: " . $currencySymbol . number_format($total, 2) . ") Tj\n";
    $text .= "0 -15 Td\n";
    $text .= "(Number of Expenses: " . count($expenses) . ") Tj\n";
    
    if ($trip['budget']) {
        $text .= "0 -15 Td\n";
        $text .= "(Budget: " . $currencySymbol . number_format($trip['budget'], 2) . ") Tj\n";
        $text .= "0 -15 Td\n";
        $remaining = $trip['budget'] - $total;
        $text .= "(Remaining: " . $currencySymbol . number_format($remaining, 2) . ") Tj\n";
    }
    
    // Category breakdown
    $text .= "0 -30 Td\n";
    $text .= "/F2 14 Tf\n";
    $text .= "(EXPENSE BREAKDOWN) Tj\n";
    $text .= "0 -20 Td\n";
    $text .= "/F1 10 Tf\n";
    
    foreach ($breakdown as $item) {
        $percentage = $total > 0 ? round(($item['total_amount'] / $total) * 100, 1) : 0;
        $text .= "0 -15 Td\n";
        $text .= "(" . substr($item['category'], 0, 20) . ": " . $currencySymbol . number_format($item['total_amount'], 2) . " (" . $percentage . "%)) Tj\n";
    }
    
    // Recent expenses
    $text .= "0 -30 Td\n";
    $text .= "/F2 14 Tf\n";
    $text .= "(RECENT EXPENSES) Tj\n";
    $text .= "0 -20 Td\n";
    $text .= "/F1 9 Tf\n";
    
    $count = 0;
    foreach (array_slice($expenses, 0, 15) as $expense) {
        $text .= "0 -12 Td\n";
        $text .= "(" . $expense['date'] . " - " . substr($expense['category'], 0, 15) . ") Tj\n";
        $text .= "0 -10 Td\n";
        $text .= "(" . substr($expense['description'], 0, 40) . ") Tj\n";
        $text .= "0 -10 Td\n";
        $text .= "(" . $currencySymbol . number_format($expense['amount'], 2) . " by " . substr($expense['paid_by_name'], 0, 15) . ") Tj\n";
        $count++;
        if ($count >= 10) break;
    }
    
    if (count($expenses) > 15) {
        $text .= "0 -15 Td\n";
        $text .= "(... and " . (count($expenses) - 15) . " more expenses) Tj\n";
    }
    
    $text .= "ET\n";
    
    $content .= "4 0 obj\n<< /Length " . strlen($text) . " >>\nstream\n" . $text . "\nendstream\nendobj\n";
    $content .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    $content .= "6 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n";
    
    $xrefPos = strlen($content);
    $content .= "xref\n0 7\n0000000000 65535 f \n0000000010 00000 n \n0000000053 00000 n \n0000000125 00000 n \n0000000348 00000 n \n";
    $content .= sprintf("%010d 00000 n \n", strpos($content, "5 0 obj"));
    $content .= sprintf("%010d 00000 n \n", strpos($content, "6 0 obj"));
    $content .= "trailer\n<< /Size 7 /Root 1 0 R >>\nstartxref\n" . $xrefPos . "\n%%EOF\n";
    
    return $content;
}
?>