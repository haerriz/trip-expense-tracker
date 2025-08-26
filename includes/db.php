<?php
// Check if running on production
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'expenses.haerriz.com') {
    // Production configuration
    $host = 'localhost';
    $dbname = 'u434561653_expenses';
    $username = 'u434561653_expenses';
    $password = 'P+ImTaJxU$2h';
} else {
    // Local development configuration
    $host = 'localhost';
    $dbname = 'trip_expense_tracker';
    $username = 'root';
    $password = 'admin@123';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    
    // Return JSON error for API calls
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
    
    die("Connection failed: " . $e->getMessage());
}
?>