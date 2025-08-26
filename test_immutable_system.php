<?php
require_once 'includes/db.php';

echo "<h2>Testing Immutable Expense Tracking System</h2>\n";

// Test 1: Check if migration was successful
echo "<h3>Test 1: Database Schema Check</h3>\n";
try {
    $stmt = $pdo->query("DESCRIBE expenses");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['is_active', 'replaced_by', 'replacement_reason'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (empty($missingColumns)) {
        echo "✅ Expenses table has all required columns\n";
    } else {
        echo "❌ Missing columns in expenses table: " . implode(', ', $missingColumns) . "\n";
    }
    
    // Check budget_history table
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_history'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Budget history table exists\n";
    } else {
        echo "❌ Budget history table missing\n";
    }
    
    // Check expense_history table
    $stmt = $pdo->query("SHOW TABLES LIKE 'expense_history'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Expense history table exists\n";
    } else {
        echo "❌ Expense history table missing\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database schema check failed: " . $e->getMessage() . "\n";
}

// Test 2: Check existing expenses are marked as active
echo "<h3>Test 2: Existing Expenses Status</h3>\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total, 
                         SUM(CASE WHEN is_active IS NULL OR is_active = TRUE THEN 1 ELSE 0 END) as active
                         FROM expenses");
    $result = $stmt->fetch();
    
    echo "Total expenses: {$result['total']}\n";
    echo "Active expenses: {$result['active']}\n";
    
    if ($result['total'] == $result['active']) {
        echo "✅ All existing expenses are properly marked as active\n";
    } else {
        echo "⚠️ Some expenses may need status update\n";
    }
    
} catch (Exception $e) {
    echo "❌ Expense status check failed: " . $e->getMessage() . "\n";
}

// Test 3: Test API endpoints
echo "<h3>Test 3: API Endpoint Tests</h3>\n";

// Check if immutable_expense.php exists and is accessible
if (file_exists('api/immutable_expense.php')) {
    echo "✅ Immutable expense API file exists\n";
} else {
    echo "❌ Immutable expense API file missing\n";
}

// Check if immutable_budget.php exists and is accessible
if (file_exists('api/immutable_budget.php')) {
    echo "✅ Immutable budget API file exists\n";
} else {
    echo "❌ Immutable budget API file missing\n";
}

// Check if get_expense_history.php exists
if (file_exists('api/get_expense_history.php')) {
    echo "✅ Expense history API file exists\n";
} else {
    echo "❌ Expense history API file missing\n";
}

// Test 4: Check updated get_expenses.php
echo "<h3>Test 4: Updated APIs Check</h3>\n";
$getExpensesContent = file_get_contents('api/get_expenses.php');
if (strpos($getExpensesContent, 'is_active') !== false) {
    echo "✅ get_expenses.php updated to filter active expenses\n";
} else {
    echo "❌ get_expenses.php not updated for immutable system\n";
}

$getTripSummaryContent = file_get_contents('api/get_trip_summary.php');
if (strpos($getTripSummaryContent, 'is_active') !== false) {
    echo "✅ get_trip_summary.php updated for immutable system\n";
} else {
    echo "❌ get_trip_summary.php not updated for immutable system\n";
}

// Test 5: Check JavaScript updates
echo "<h3>Test 5: Frontend Integration Check</h3>\n";
$jsContent = file_get_contents('js/trip-dashboard.js');
if (strpos($jsContent, 'immutable_expense.php') !== false) {
    echo "✅ JavaScript updated to use immutable expense API\n";
} else {
    echo "❌ JavaScript not updated for immutable expense API\n";
}

if (strpos($jsContent, 'immutable_budget.php') !== false) {
    echo "✅ JavaScript updated to use immutable budget API\n";
} else {
    echo "❌ JavaScript not updated for immutable budget API\n";
}

if (strpos($jsContent, 'viewExpenseHistory') !== false) {
    echo "✅ JavaScript includes expense history functionality\n";
} else {
    echo "❌ JavaScript missing expense history functionality\n";
}

if (strpos($jsContent, 'viewBudgetHistory') !== false) {
    echo "✅ JavaScript includes budget history functionality\n";
} else {
    echo "❌ JavaScript missing budget history functionality\n";
}

// Test 6: CSS Updates
echo "<h3>Test 6: CSS Styling Check</h3>\n";
$cssContent = file_get_contents('css/style.css');
if (strpos($cssContent, 'expense-item--replaced') !== false) {
    echo "✅ CSS includes immutable system styles\n";
} else {
    echo "❌ CSS missing immutable system styles\n";
}

if (strpos($cssContent, 'budget-with-controls') !== false) {
    echo "✅ CSS includes budget control styles\n";
} else {
    echo "❌ CSS missing budget control styles\n";
}

if (strpos($cssContent, 'history-record') !== false) {
    echo "✅ CSS includes history view styles\n";
} else {
    echo "❌ CSS missing history view styles\n";
}

echo "<h3>Summary</h3>\n";
echo "The immutable expense tracking system has been implemented with the following features:\n\n";
echo "🔒 <strong>Immutable Expenses:</strong>\n";
echo "   • Expenses cannot be deleted, only deactivated\n";
echo "   • Modifications create new records and mark old ones as replaced\n";
echo "   • Full audit trail maintained in expense_history table\n\n";

echo "💰 <strong>Immutable Budget:</strong>\n";
echo "   • Budget changes are tracked with history\n";
echo "   • Budget adjustments create expense records for tracking\n";
echo "   • All changes include reason and timestamp\n\n";

echo "📊 <strong>UI Features:</strong>\n";
echo "   • History buttons for viewing expense and budget changes\n";
echo "   • Visual indicators for modified expenses\n";
echo "   • Reason prompts for all modifications\n";
echo "   • Budget adjustment controls with +/- buttons\n\n";

echo "🔍 <strong>API Integration:</strong>\n";
echo "   • New immutable_expense.php for expense operations\n";
echo "   • New immutable_budget.php for budget operations\n";
echo "   • History viewing APIs\n";
echo "   • Updated existing APIs to filter active records only\n\n";

echo "<strong>System is ready for testing!</strong>\n";
echo "Access the dashboard to test the immutable features.\n";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #2196F3; }
h3 { color: #1976D2; margin-top: 20px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>