<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(DISTINCT tm.trip_id) as trip_count,
               COALESCE(SUM(e.amount), 0) as total_expenses
        FROM users u
        LEFT JOIN trip_members tm ON u.id = tm.user_id AND (tm.status = 'accepted' OR tm.status IS NULL)
        LEFT JOIN expenses e ON u.id = e.paid_by
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'users' => $users]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>