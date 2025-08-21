<?php
// Production configuration for expenses.haerriz.com

// Database configuration for production
$production_config = [
    'host' => 'localhost',
    'dbname' => 'u434561653_expenses',
    'username' => 'u434561653_expenses',
    'password' => 'P+ImTaJxU$2h',
    'charset' => 'utf8mb4'
];

// Production settings
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');

// Security headers for production
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Production database connection
if ($_SERVER['HTTP_HOST'] === 'expenses.haerriz.com') {
    $host = $production_config['host'];
    $dbname = $production_config['dbname'];
    $username = $production_config['username'];
    $password = $production_config['password'];
    $charset = $production_config['charset'];
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    
    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed");
    }
}
?>