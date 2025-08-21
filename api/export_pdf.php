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

// Simple HTML to PDF conversion
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="trip-expenses-' . preg_replace('/[^a-zA-Z0-9]/', '-', $trip['name']) . '.pdf"');

// For now, output as HTML since proper PDF generation requires additional libraries
// In production, you would use libraries like TCPDF, FPDF, or wkhtmltopdf
header('Content-Type: text/html');
header('Content-Disposition: inline; filename="trip-expenses-' . preg_replace('/[^a-zA-Z0-9]/', '-', $trip['name']) . '.html"');
echo $html;
?>