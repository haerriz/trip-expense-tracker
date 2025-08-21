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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create tables if they don't exist (for production)
    if ($_SERVER['HTTP_HOST'] === 'expenses.haerriz.com') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            password VARCHAR(255) NOT NULL,
            picture TEXT,
            phone_verified TINYINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
} catch(PDOException $e) {
    // Return JSON error for API calls
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }
    die("Connection failed: " . $e->getMessage());
}
?>