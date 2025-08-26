<?php
session_start();
require_once 'includes/db.php';

echo "<h2>Authentication Test</h2>\n";

echo "<h3>Session Data:</h3>\n";
echo "<pre>" . print_r($_SESSION, true) . "</pre>\n";

echo "<h3>Database Connection:</h3>\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    echo "✅ Database connected, users count: " . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "<h3>Test API Call:</h3>\n";
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "\n";
    echo "User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "\n";
    
    // Test the API directly
    $_GET['trip_id'] = 2;
    ob_start();
    try {
        include 'api/get_trip_summary.php';
        $response = ob_get_clean();
        echo "<h4>API Response:</h4>\n";
        echo "<pre>" . $response . "</pre>\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ API Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ No user session found\n";
    echo "<p><a href='login.php'>Login here</a></p>\n";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #2196F3; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>