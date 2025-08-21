<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $tripId = $_GET['trip_id'];
    
    if (!$tripId) {
        echo json_encode(['success' => false, 'error' => 'Trip ID required']);
        exit;
    }
    
    // Get trip details
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        echo json_encode(['success' => false, 'error' => 'Trip not found']);
        exit;
    }
    
    // Get total expenses
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE trip_id = ?");
    $stmt->execute([$tripId]);
    $totalExpenses = $stmt->fetchColumn();
    
    // Get member count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ?");
    $stmt->execute([$tripId]);
    $memberCount = $stmt->fetchColumn();
    
    // Calculate remaining budget
    $remaining = $trip['budget'] - $totalExpenses;
    $perPersonShare = $memberCount > 0 ? $totalExpenses / $memberCount : 0;
    
    echo json_encode([
        'success' => true,
        'trip' => $trip,
        'total_expenses' => $totalExpenses,
        'remaining_budget' => $remaining,
        'member_count' => $memberCount,
        'per_person_share' => $perPersonShare
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>