<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $tripId = $_GET['trip_id'] ?? null;
    $expenseId = $_GET['expense_id'] ?? null;
    
    if (!$tripId) {
        echo json_encode(['success' => false, 'message' => 'Trip ID required']);
        exit;
    }
    
    // Verify user is member of the trip
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ?");
    $stmt->execute([$tripId, $_SESSION['user_id']]);
    
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    if ($expenseId) {
        // Get history for specific expense
        $stmt = $pdo->prepare("
            SELECT eh.*, u1.name as original_paid_by_name, u2.name as changed_by_name
            FROM expense_history eh
            JOIN users u1 ON eh.paid_by = u1.id
            JOIN users u2 ON eh.changed_by = u2.id
            WHERE eh.original_expense_id = ?
            ORDER BY eh.created_at DESC
        ");
        $stmt->execute([$expenseId]);
    } else {
        // Get all expense history for trip
        $stmt = $pdo->prepare("
            SELECT eh.*, u1.name as original_paid_by_name, u2.name as changed_by_name
            FROM expense_history eh
            JOIN users u1 ON eh.paid_by = u1.id
            JOIN users u2 ON eh.changed_by = u2.id
            WHERE eh.trip_id = ?
            ORDER BY eh.created_at DESC
        ");
        $stmt->execute([$tripId]);
    }
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>