<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

try {
    $userId = $_GET['user_id'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
        exit;
    }
    
    // Get user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Get user's trips
    $stmt = $pdo->prepare("
        SELECT t.*, COUNT(DISTINCT tm.user_id) as member_count,
               COALESCE(SUM(e.amount), 0) as total_expenses
        FROM trips t
        LEFT JOIN trip_members tm ON t.id = tm.trip_id AND (tm.status = 'accepted' OR tm.status IS NULL)
        LEFT JOIN expenses e ON t.id = e.trip_id
        WHERE t.created_by = ? OR t.id IN (
            SELECT trip_id FROM trip_members WHERE user_id = ? AND (status = 'accepted' OR status IS NULL)
        )
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$userId, $userId]);
    $trips = $stmt->fetchAll();
    
    // Get user's total expenses
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total_expenses FROM expenses WHERE paid_by = ?");
    $stmt->execute([$userId]);
    $totalExpenses = $stmt->fetchColumn();
    
    $user['total_expenses'] = $totalExpenses;
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'trips' => $trips
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>