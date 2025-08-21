<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.name as creator_name,
               COUNT(DISTINCT tm.user_id) as member_count,
               COALESCE(SUM(e.amount), 0) as total_expenses
        FROM trips t
        LEFT JOIN users u ON t.created_by = u.id
        LEFT JOIN trip_members tm ON t.id = tm.trip_id AND (tm.status = 'accepted' OR tm.status IS NULL)
        LEFT JOIN expenses e ON t.id = e.trip_id
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
    $trips = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'trips' => $trips]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>