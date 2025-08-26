<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$trip_id = $_POST['trip_id'] ?? null;
$action = $_POST['action'] ?? null;
$amount = $_POST['amount'] ?? null;

if (!$trip_id || !$action || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!in_array($action, ['increase', 'decrease'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$amount = floatval($amount);
if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Amount must be positive']);
    exit;
}

try {
    // Check if user is trip creator or master admin
    $stmt = $pdo->prepare("SELECT created_by, budget FROM trips WHERE id = ?");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        echo json_encode(['success' => false, 'message' => 'Trip not found']);
        exit;
    }
    
    $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';
    $isCreator = $trip['created_by'] == $_SESSION['user_id'];
    
    if (!$isCreator && !$isMasterAdmin) {
        echo json_encode(['success' => false, 'message' => 'Only trip creator can adjust budget']);
        exit;
    }
    
    $currentBudget = $trip['budget'];
    if ($currentBudget === null) {
        echo json_encode(['success' => false, 'message' => 'No budget set for this trip']);
        exit;
    }
    
    $newBudget = $action === 'increase' ? 
        $currentBudget + $amount : 
        max(0, $currentBudget - $amount);
    
    $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ?");
    $stmt->execute([$newBudget, $trip_id]);
    
    $actionText = $action === 'increase' ? 'increased' : 'decreased';
    echo json_encode([
        'success' => true, 
        'message' => "Budget {$actionText} successfully",
        'new_budget' => $newBudget
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>