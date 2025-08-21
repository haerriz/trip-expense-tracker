<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $budget = $_POST['budget'] ?? '';
        $noBudget = $_POST['no_budget'] ?? false;
        $userId = $_SESSION['user_id'];
        
        if (empty($tripId)) {
            echo json_encode(['success' => false, 'message' => 'Trip ID required']);
            exit;
        }
        
        // Verify user is trip creator or master admin
        $stmt = $pdo->prepare("SELECT created_by, name FROM trips WHERE id = ?");
        $stmt->execute([$tripId]);
        $trip = $stmt->fetch();
        
        if (!$trip) {
            echo json_encode(['success' => false, 'message' => 'Trip not found']);
            exit;
        }
        
        $isMasterAdmin = ($_SESSION['user_email'] === 'haerriz@gmail.com');
        $isCreator = ($trip['created_by'] == $userId);
        
        if (!$isCreator && !$isMasterAdmin) {
            echo json_encode(['success' => false, 'message' => 'Only trip creator or master admin can edit budget']);
            exit;
        }
        
        // Handle budget update
        if ($noBudget === 'true' || $noBudget === true) {
            // Set budget to NULL for no budget mode
            $stmt = $pdo->prepare("UPDATE trips SET budget = NULL WHERE id = ?");
            $stmt->execute([$tripId]);
            $message = 'Budget removed - trip now has no budget limit';
        } else {
            // Validate and set budget
            $budgetAmount = floatval($budget);
            if ($budgetAmount < 0) {
                echo json_encode(['success' => false, 'message' => 'Budget cannot be negative']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ?");
            $stmt->execute([$budgetAmount, $tripId]);
            $message = $budgetAmount > 0 ? "Budget updated to $" . number_format($budgetAmount, 2) : 'Budget set to $0.00';
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>