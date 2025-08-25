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

// Generate enhanced HTML for PDF
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Trip Expense Report - <?= htmlspecialchars($trip['name']) ?></title>
    <style>
        @page {
            margin: 20mm;
            size: A4;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 3px solid #2196F3;
        }
        
        .header h1 {
            color: #2196F3;
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header h2 {
            color: #666;
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: 400;
        }
        
        .header .date {
            color: #888;
            font-size: 14px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        
        .summary-card {
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
            font-size: 18px;
            color: #2196F3;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 15px;
        }
        
        .section {
            margin: 40px 0;
        }
        
        .section h3 {
            color: #2196F3;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .professional-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .professional-table thead {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
        }
        
        .professional-table th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .professional-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        .professional-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .professional-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        .professional-table tbody tr:hover {
            background-color: #e3f2fd;
        }
        
        .amount {
            font-weight: 600;
            color: #2196F3;
        }
        
        .percentage {
            color: #666;
            font-style: italic;
        }
        
        .progress-bar {
            background: #e0e0e0;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin: 5px 0;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #2196F3, #1976D2);
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .expense-detail {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .expense-detail:nth-child(even) {
            background: #f8f9fa;
        }
        
        .expense-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .expense-category {
            font-weight: 600;
            color: #2196F3;
            font-size: 16px;
        }
        
        .expense-amount {
            font-weight: bold;
            color: #1976D2;
            font-size: 16px;
        }
        
        .expense-description {
            color: #666;
            margin: 5px 0;
            font-style: italic;
        }
        
        .expense-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #888;
            margin-top: 8px;
        }
        
        .footer {
            margin-top: 50px;
            padding: 20px 0;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
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
        .mb-0 { margin-bottom: 0; }
        .mt-20 { margin-top: 20px; }
        
        /* Floating Action Buttons */
        .fab-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .fab {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .fab:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        }
        
        .fab-download {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }
        
        .fab-print {
            background: linear-gradient(135deg, #2196F3, #1976D2);
        }
        
        .fab-back {
            background: linear-gradient(135deg, #FF9800, #F57C00);
        }
        
        /* Tooltip */
        .fab::before {
            content: attr(data-tooltip);
            position: absolute;
            right: 70px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        
        .fab:hover::before {
            opacity: 1;
        }
        
        /* Print styles */
        @media print {
            .fab-container {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .header {
                page-break-after: avoid;
            }
            
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($trip['name']) ?></h1>
        <h2>Trip Expense Report</h2>
        <div class="date">Generated on <?= date('F j, Y \a\t g:i A') ?></div>
    </div>

    <div class="summary-grid">
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

    <div class="section">
        <h3>Expense Breakdown by Category</h3>
        <table class="professional-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Percentage</th>
                    <th>Count</th>
                    <th>Visual Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($breakdown as $index => $item): ?>
                    <?php $percentage = $total > 0 ? ($item['total_amount'] / $total) * 100 : 0; ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($item['category']) ?></strong></td>
                        <td class="amount"><?= $currencySymbol . number_format($item['total_amount'], 2) ?></td>
                        <td class="percentage"><?= round($percentage, 1) ?>%</td>
                        <td class="text-center"><?= $item['expense_count'] ?></td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min($percentage, 100) ?>%;"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Member Contributions</h3>
        <table class="professional-table">
            <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Total Paid</th>
                    <th>Percentage</th>
                    <th>Share</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <?php 
                    $percentage = $total > 0 ? ($member['total_paid'] / $total) * 100 : 0;
                    $perPersonShare = count($members) > 0 ? $total / count($members) : 0;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($member['name']) ?></strong></td>
                        <td class="amount"><?= $currencySymbol . number_format($member['total_paid'], 2) ?></td>
                        <td class="percentage"><?= round($percentage, 1) ?>%</td>
                        <td class="amount"><?= $currencySymbol . number_format($perPersonShare, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Detailed Expense List</h3>
        <?php foreach ($expenses as $index => $expense): ?>
            <div class="expense-detail">
                <div class="expense-header">
                    <div class="expense-category">
                        <?= htmlspecialchars($expense['category']) ?>
                        <?php if ($expense['subcategory']): ?>
                            <span style="color: #666; font-weight: normal;"> • <?= htmlspecialchars($expense['subcategory']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="expense-amount"><?= $currencySymbol . number_format($expense['amount'], 2) ?></div>
                </div>
                <div class="expense-description"><?= htmlspecialchars($expense['description']) ?></div>
                <div class="expense-meta">
                    <span>Paid by: <strong><?= htmlspecialchars($expense['paid_by_name']) ?></strong></span>
                    <span>Date: <strong><?= date('M j, Y', strtotime($expense['date'])) ?></strong></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="footer">
        <p><strong>Trip Finance Report</strong> • Generated on <?= date('F j, Y \a\t g:i A') ?></p>
        <p>Total Expenses: <strong><?= $currencySymbol . number_format($total, 2) ?></strong> • 
           <?= count($expenses) ?> transactions • <?= count($members) ?> members</p>
    </div>
    
    <!-- Floating Action Buttons -->
    <div class="fab-container">
        <button class="fab fab-download" onclick="downloadPDF()" data-tooltip="Download PDF">
            ⬇
        </button>
        <button class="fab fab-print" onclick="printReport()" data-tooltip="Print Report">
            🖨
        </button>
        <a href="../dashboard.php" class="fab fab-back" data-tooltip="Back to Dashboard">
            ←
        </a>
    </div>
    
    <script>
        function downloadPDF() {
            // Create filename
            const tripName = '<?= preg_replace('/[^a-zA-Z0-9]/', '-', $trip['name']) ?>';
            const filename = `trip-expenses-${tripName}-<?= date('Y-m-d') ?>.pdf`;
            
            // Use browser's print to PDF functionality
            const printWindow = window.open('', '_blank');
            printWindow.document.write(document.documentElement.outerHTML);
            printWindow.document.close();
            printWindow.focus();
            
            // Trigger print dialog
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }
        
        function printReport() {
            window.print();
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 's':
                        e.preventDefault();
                        downloadPDF();
                        break;
                    case 'p':
                        e.preventDefault();
                        printReport();
                        break;
                }
            }
        });
        
        // Show success message
        setTimeout(() => {
            const message = document.createElement('div');
            message.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: #4CAF50;
                color: white;
                padding: 12px 24px;
                border-radius: 4px;
                z-index: 1001;
                font-size: 14px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            `;
            message.textContent = 'Report generated successfully! Use the buttons below to download or print.';
            document.body.appendChild(message);
            
            setTimeout(() => {
                message.remove();
            }, 4000);
        }, 1000);
    </script>
</body>
</html>
<?php
$html = ob_get_clean();

// Set headers for PDF download
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: inline; filename="trip-expenses-' . preg_replace('/[^a-zA-Z0-9]/', '-', $trip['name']) . '.html"');

// For now, output HTML (can be converted to PDF using browser print or wkhtmltopdf)
echo $html;
?>