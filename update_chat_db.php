<?php
// Simple database schema update for chat features
$host = 'localhost';
$dbname = 'trip_expense_tracker';
$username = 'root';
$password = 'admin@123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Create chat_messages table if it doesn't exist
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            trip_id INT NOT NULL,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            file_url VARCHAR(500) NULL,
            file_name VARCHAR(255) NULL,
            file_size INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ";
    
    try {
        $pdo->exec($createTableQuery);
        echo "✓ Created chat_messages table\n";
    } catch (PDOException $e) {
        echo "- Chat messages table already exists\n";
    }
    
    // Add file columns to chat_messages table if they don't exist (for existing tables)
    $alterQueries = [
        "ALTER TABLE chat_messages ADD COLUMN file_url VARCHAR(500) NULL",
        "ALTER TABLE chat_messages ADD COLUMN file_name VARCHAR(255) NULL",
        "ALTER TABLE chat_messages ADD COLUMN file_size INT NULL"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            echo "✓ Executed: " . $query . "\n";
        } catch (PDOException $e) {
            // Column might already exist
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "- Column already exists, skipping...\n";
            } else {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✅ Chat schema update completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error connecting to database: " . $e->getMessage() . "\n";
}
?>