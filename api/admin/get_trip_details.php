<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

$tripId = $_GET['trip_id'] ?? null;

if (!$tripId) {
    echo json_encode(['success' => false, 'message' => 'Trip ID required']);
    exit;
}

try {
    // Get trip details
    $stmt = $pdo->prepare("
        SELECT t.*, u.name as creator_name 
        FROM trips t 
        JOIN users u ON t.created_by = u.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        echo json_encode(['success' => false, 'message' => 'Trip not found']);
        exit;
    }
    
    // Get trip members with their expenses
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.picture,
               COALESCE(SUM(e.amount), 0) as total_paid
        FROM trip_members tm
        JOIN users u ON tm.user_id = u.id
        LEFT JOIN expenses e ON e.paid_by = u.id AND e.trip_id = ?
        WHERE tm.trip_id = ?
        GROUP BY u.id, u.name, u.email, u.picture
        ORDER BY total_paid DESC
    ");
    $stmt->execute([$tripId, $tripId]);
    $members = $stmt->fetchAll();
    
    // Get recent expenses
    $stmt = $pdo->prepare("
        SELECT e.*, u.name as paid_by_name
        FROM expenses e
        JOIN users u ON e.paid_by = u.id
        WHERE e.trip_id = ?
        ORDER BY e.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$tripId]);
    $expenses = $stmt->fetchAll();
    
    // Calculate total expenses
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE trip_id = ?");
    $stmt->execute([$tripId]);
    $totalExpenses = $stmt->fetchColumn();
    
    $trip['total_expenses'] = $totalExpenses;
    
    echo json_encode([
        'success' => true,
        'trip' => $trip,
        'members' => $members,
        'expenses' => $expenses
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>