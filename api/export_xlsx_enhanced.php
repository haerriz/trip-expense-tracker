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

// Get expense breakdown
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
$currencySymbols = [
    'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 
    'AUD' => 'A$', 'CAD' => 'C$', 'INR' => '₹', 'THB' => '฿', 'VND' => '₫'
];
$currencySymbol = $currencySymbols[$currency] ?? '$';

// Generate enhanced HTML for Excel
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Trip Expense Report - <?= htmlspecialchars($trip['name']) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            border-radius: 8px;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 16px;
            opacity: 0.9;
        }
        
        .summary-section {
            display: flex;
            gap: 20px;
            margin: 30px 0;
        }
        
        .summary-card {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
        }
        
        .summary-card h3 {
            margin: 0 0 15px 0;
            color: #2196F3;
            font-size: 16px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
        }
        
        .summary-item.total {
            font-weight: bold;
            font-size: 16px;
            color: #2196F3;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 15px;
        }
        
        .professional-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .professional-table thead {
            background: #2196F3;
            color: white;
        }
        
        .professional-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #ddd;
        }
        
        .professional-table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        
        .professional-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .professional-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        .amount {
            font-weight: 600;
            color: #2196F3;
            text-align: right;
        }
        
        .percentage {
            color: #666;
            text-align: center;
        }
        
        .section-title {
            color: #2196F3;
            font-size: 18px;
            margin: 30px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .footer {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            color: #666;
        }
        
        .badge {
            background: #2196F3;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($trip['name']) ?></h1>
        <h2>Trip Expense Report</h2>
        <div>Generated on <?= date('F j, Y \a\t g:i A') ?></div>
    </div>

    <div class="summary-section">
        <div class="summary-card">
            <h3>Financial Summary</h3>
            <?php if ($trip['budget']): ?>
                <div class="summary-item">
                    <span>Trip Budget:</span>
                    <span class="amount"><?= $currencySymbol . number_format($trip['budget'], 2) ?></span>
                </div>
            <?php endif; ?>
            <div class="summary-item">
                <span>Total Expenses:</span>
                <span class="amount"><?= $currencySymbol . number_format($total, 2) ?></span>
            </div>
            <?php if ($trip['budget']): ?>
                <div class="summary-item">
                    <span>Remaining Budget:</span>
                    <span class="amount"><?= $currencySymbol . number_format($trip['budget'] - $total, 2) ?></span>
                </div>
                <div class="summary-item total">
                    <span>Budget Usage:</span>
                    <span><?= round(($total / $trip['budget']) * 100, 1) ?>%</span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="summary-card">
            <h3>Trip Statistics</h3>
            <div class="summary-item">
                <span>Total Expenses:</span>
                <span class="badge"><?= count($expenses) ?></span>
            </div>
            <div class="summary-item">
                <span>Trip Members:</span>
                <span class="badge"><?= count($members) ?></span>
            </div>
            <div class="summary-item">
                <span>Categories Used:</span>
                <span class="badge"><?= count($breakdown) ?></span>
            </div>
            <div class="summary-item">
                <span>Currency:</span>
                <span class="badge"><?= $currency ?></span>
            </div>
        </div>
    </div>

    <h3 class="section-title">Expense Breakdown by Category</h3>
    <table class="professional-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>Percentage</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($breakdown as $item): ?>
                <?php $percentage = $total > 0 ? ($item['total_amount'] / $total) * 100 : 0; ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['category']) ?></strong></td>
                    <td class="amount"><?= $currencySymbol . number_format($item['total_amount'], 2) ?></td>
                    <td class="percentage"><?= round($percentage, 1) ?>%</td>
                    <td class="text-center"><?= $item['expense_count'] ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background: #e3f2fd; font-weight: bold;">
                <td><strong>TOTAL</strong></td>
                <td class="amount"><strong><?= $currencySymbol . number_format($total, 2) ?></strong></td>
                <td class="percentage"><strong>100%</strong></td>
                <td class="text-center"><strong><?= count($expenses) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <h3 class="section-title">Member Contributions</h3>
    <table class="professional-table">
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Total Paid</th>
                <th>Percentage</th>
                <th>Equal Share</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $perPersonShare = count($members) > 0 ? $total / count($members) : 0;
            foreach ($members as $member): 
                $percentage = $total > 0 ? ($member['total_paid'] / $total) * 100 : 0;
                $balance = $member['total_paid'] - $perPersonShare;
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($member['name']) ?></strong></td>
                    <td class="amount"><?= $currencySymbol . number_format($member['total_paid'], 2) ?></td>
                    <td class="percentage"><?= round($percentage, 1) ?>%</td>
                    <td class="amount"><?= $currencySymbol . number_format($perPersonShare, 2) ?></td>
                    <td class="amount" style="color: <?= $balance >= 0 ? '#4CAF50' : '#F44336' ?>">
                        <?= ($balance >= 0 ? '+' : '') . $currencySymbol . number_format($balance, 2) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3 class="section-title">Detailed Expense List</h3>
    <table class="professional-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Paid By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $expense): ?>
                <tr>
                    <td><?= date('M j, Y', strtotime($expense['date'])) ?></td>
                    <td><strong><?= htmlspecialchars($expense['category']) ?></strong></td>
                    <td><?= htmlspecialchars($expense['subcategory'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($expense['description']) ?></td>
                    <td class="amount"><?= $currencySymbol . number_format($expense['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($expense['paid_by_name']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <h3>Report Summary</h3>
        <p><strong>Total Trip Expenses:</strong> <?= $currencySymbol . number_format($total, 2) ?></p>
        <p><?= count($expenses) ?> transactions • <?= count($members) ?> members • <?= count($breakdown) ?> categories</p>
        <p>Report generated on <?= date('F j, Y \a\t g:i A') ?></p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Set headers for Excel download
$filename = 'trip-expenses-' . preg_replace('/[^a-zA-Z0-9]/', '-', $trip['name']) . '-' . date('Y-m-d') . '.xls';
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Add BOM for UTF-8
echo "\xEF\xBB\xBF";
echo $html;
?>