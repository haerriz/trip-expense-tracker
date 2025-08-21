<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

try {
    // Get total users
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users");
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();
    
    // Get total trips
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_trips FROM trips");
    $stmt->execute();
    $totalTrips = $stmt->fetchColumn();
    
    // Get total expenses
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total_expenses FROM expenses");
    $stmt->execute();
    $totalExpenses = $stmt->fetchColumn();
    
    // Get active trips (trips with recent activity)
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT t.id) as active_trips 
        FROM trips t 
        LEFT JOIN expenses e ON t.id = e.trip_id 
        WHERE e.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) OR t.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $activeTrips = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => $totalUsers,
            'total_trips' => $totalTrips,
            'total_expenses' => $totalExpenses,
            'active_trips' => $activeTrips
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>