<?php
require_once 'includes/db.php';

echo "<h2>Debug Trip Summary Issue</h2>\n";

$tripId = 2; // Using the existing trip ID

try {
    // Get trip details
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch();
    
    echo "<h3>Trip Data:</h3>\n";
    echo "<pre>" . print_r($trip, true) . "</pre>\n";
    
    // Get total expenses (excluding budget category, only active expenses)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE trip_id = ? AND category != 'Budget' AND category != 'Budget Adjustment' AND (is_active IS NULL OR is_active = TRUE)");
    $stmt->execute([$tripId]);
    $totalExpenses = $stmt->fetchColumn();
    
    echo "<h3>Total Expenses Query Result:</h3>\n";
    echo "Total: $totalExpenses\n";
    
    // Debug: Check all expenses for this trip
    $stmt = $pdo->prepare("SELECT id, amount, category, is_active FROM expenses WHERE trip_id = ?");
    $stmt->execute([$tripId]);
    $allExpenses = $stmt->fetchAll();
    
    echo "<h3>All Expenses for Trip:</h3>\n";
    echo "<pre>" . print_r($allExpenses, true) . "</pre>\n";
    
    // Get member count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ?");
    $stmt->execute([$tripId]);
    $memberCount = $stmt->fetchColumn();
    
    echo "<h3>Member Count:</h3>\n";
    echo "Members: $memberCount\n";
    
    // Calculate remaining budget
    $remaining = $trip['budget'] ? $trip['budget'] - $totalExpenses : null;
    $perPersonShare = $memberCount > 0 ? $totalExpenses / $memberCount : 0;
    
    echo "<h3>Calculations:</h3>\n";
    echo "Budget: " . $trip['budget'] . "\n";
    echo "Total Expenses: $totalExpenses\n";
    echo "Remaining: $remaining\n";
    echo "Per Person Share: $perPersonShare\n";
    echo "Currency: " . $trip['currency'] . "\n";
    
    // Test the exact query from the API
    echo "<h3>Testing Exact API Query:</h3>\n";
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE trip_id = ? AND category != 'Budget' AND category != 'Budget Adjustment' AND (is_active IS NULL OR is_active = TRUE)");
    $stmt->execute([$tripId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Query result: " . print_r($result, true) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #2196F3; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>