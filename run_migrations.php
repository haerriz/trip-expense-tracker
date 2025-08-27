<?php
// Automated migration runner for Hostinger auto-deploy
require_once __DIR__ . '/includes/db.php';

$migrationsDir = __DIR__ . '/config/';
$files = glob($migrationsDir . '*.sql');
foreach ($files as $file) {
    $sql = file_get_contents($file);
    try {
        $pdo->exec($sql);
        echo "Migration applied: $file\n";
    } catch (PDOException $e) {
        echo "Error in $file: " . $e->getMessage() . "\n";
    }
}
?>
