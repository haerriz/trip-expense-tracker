<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    echo "Database connection successful! Users table exists.";
} catch(Exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>