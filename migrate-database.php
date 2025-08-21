<?php
require_once 'config/database.php';

header('Content-Type: text/html');

try {
    echo "<h2>Migrating Database Schema...</h2>";
    
    // Check if status column exists in trip_members
    $stmt = $pdo->query("SHOW COLUMNS FROM trip_members LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        echo "<p>Adding status column to trip_members...</p>";
        $pdo->exec("ALTER TABLE trip_members ADD COLUMN status ENUM('pending', 'accepted', 'rejected') DEFAULT 'accepted'");
        echo "<p>✅ Status column added</p>";
    } else {
        echo "<p>✅ Status column already exists</p>";
    }
    
    // Check if invited_by column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM trip_members LIKE 'invited_by'");
    if ($stmt->rowCount() == 0) {
        echo "<p>Adding invited_by column to trip_members...</p>";
        $pdo->exec("ALTER TABLE trip_members ADD COLUMN invited_by INT");
        echo "<p>✅ Invited_by column added</p>";
    } else {
        echo "<p>✅ Invited_by column already exists</p>";
    }
    
    // Check if invited_at column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM trip_members LIKE 'invited_at'");
    if ($stmt->rowCount() == 0) {
        echo "<p>Adding invited_at column to trip_members...</p>";
        $pdo->exec("ALTER TABLE trip_members ADD COLUMN invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "<p>✅ Invited_at column added</p>";
    } else {
        echo "<p>✅ Invited_at column already exists</p>";
    }
    
    // Modify joined_at to allow NULL
    echo "<p>Modifying joined_at column to allow NULL...</p>";
    $pdo->exec("ALTER TABLE trip_members MODIFY COLUMN joined_at TIMESTAMP NULL");
    echo "<p>✅ Joined_at column modified</p>";
    
    // Update existing members to 'accepted' status
    echo "<p>Updating existing members to accepted status...</p>";
    $pdo->exec("UPDATE trip_members SET status = 'accepted' WHERE status IS NULL");
    echo "<p>✅ Existing members updated</p>";
    
    echo "<h3>🎉 Database migration completed successfully!</h3>";
    echo "<p><a href='/dashboard'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Migration Error: " . $e->getMessage() . "</h3>";
}
?>