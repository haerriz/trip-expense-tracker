<?php
// Automated migration runner for Hostinger auto-deploy
require_once __DIR__ . '/includes/db.php';

try {
    $migrationsDir = __DIR__ . '/config/';
    
    if (!is_dir($migrationsDir)) {
        echo "Config directory not found, skipping migrations\n";
        exit(0);
    }
    
    $files = glob($migrationsDir . '*.sql');
    
    if (empty($files)) {
        echo "No SQL migration files found, skipping migrations\n";
        exit(0);
    }
    
    foreach ($files as $file) {
        if (!file_exists($file)) {
            echo "File not found: $file, skipping\n";
            continue;
        }
        
        $sql = file_get_contents($file);
        if (empty(trim($sql))) {
            echo "Empty SQL file: $file, skipping\n";
            continue;
        }
        
        try {
            $pdo->exec($sql);
            echo "Migration applied: $file\n";
        } catch (PDOException $e) {
            echo "Error in $file: " . $e->getMessage() . "\n";
        }
    }
    
    echo "Migration runner completed\n";
    
} catch (Exception $e) {
    echo "Migration runner error: " . $e->getMessage() . "\n";
}
?>
