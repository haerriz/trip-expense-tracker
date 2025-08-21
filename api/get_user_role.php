<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    $tripId = $_GET['trip_id'] ?? '';
    $userId = $_SESSION['user_id'];
    
    if (empty($tripId)) {
        echo json_encode(['success' => false, 'message' => 'Trip ID required']);
        exit;
    }
    
    // Get trip info and user role
    $stmt = $pdo->prepare("SELECT created_by, name FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        echo json_encode(['success' => false, 'message' => 'Trip not found']);
        exit;
    }
    
    $isCreator = ($trip['created_by'] == $userId);
    
    // Check if user is member
    try {
        $stmt = $pdo->prepare("SELECT status FROM trip_members WHERE trip_id = ? AND user_id = ?");
        $stmt->execute([$tripId, $userId]);
        $membership = $stmt->fetch();
        $isMember = $membership && ($membership['status'] === 'accepted' || $membership['status'] === null);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ?");
        $stmt->execute([$tripId, $userId]);
        $isMember = $stmt->fetchColumn() > 0;
    }
    
    // Count total members
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND (status = 'accepted' OR status IS NULL)");
        $stmt->execute([$tripId]);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ?");
        $stmt->execute([$tripId]);
    }
    $memberCount = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'is_creator' => $isCreator,
        'is_member' => $isMember,
        'member_count' => $memberCount,
        'trip_name' => $trip['name']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>