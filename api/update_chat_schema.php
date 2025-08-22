<?php
require_once '../config/database.php';

// Simple authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    die('Authentication required');
}

try {
    // Add file columns to chat_messages table if they don't exist
    $alterQueries = [
        "ALTER TABLE chat_messages ADD COLUMN file_url VARCHAR(500) NULL",
        "ALTER TABLE chat_messages ADD COLUMN file_name VARCHAR(255) NULL",
        "ALTER TABLE chat_messages ADD COLUMN file_size INT NULL"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            echo "Executed: " . $query . "\n";
        } catch (PDOException $e) {
            // Column might already exist
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                echo "Error: " . $e->getMessage() . "\n";
            } else {
                echo "Column already exists, skipping...\n";
            }
        }
    }
    
    echo "Chat schema update completed!\n";
    
} catch (Exception $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
?>