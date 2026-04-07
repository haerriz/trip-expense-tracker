<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

function jsonResponse(bool $success, array $payload = []) {
    echo json_encode(array_merge(['success' => $success], $payload));
    exit;
}

function jsonError(string $message) {
    jsonResponse(false, ['message' => $message]);
}

function isTripCreator(PDO $pdo, $tripId, $userId): bool {
    $stmt = $pdo->prepare("SELECT created_by FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);

    return $trip && $trip['created_by'] == $userId;
}

function ensureBudgetCategory(PDO $pdo): void {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Budget'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, subcategories) VALUES ('Budget', 'Initial Budget,Budget Increase,Budget Decrease')");
        $stmt->execute();
    }
}

function createBudgetExpense(PDO $pdo, $tripId, $subcategory, $amount, $description, $userId): void {
    $stmt = $pdo->prepare("
        INSERT INTO expenses (trip_id, category, subcategory, amount, description, date, paid_by, created_at)
        VALUES (?, 'Budget', ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$tripId, $subcategory, $amount, $description, date('Y-m-d'), $userId]);

    $expenseId = $pdo->lastInsertId();

    // Add expense split for the creator
    $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
    $stmt->execute([$expenseId, $userId, $amount]);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonError('Invalid request method');
    }

    $tripId = trim($_POST['trip_id'] ?? '');
    $action = trim($_POST['action'] ?? ''); // 'increase' or 'decrease'
    $amount = round(floatval($_POST['amount'] ?? 0), 2);
    $userId = $_SESSION['user_id'];
    $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';

    if ($tripId === '' || $action === '' || $amount <= 0) {
        jsonError('Missing required parameters');
    }

    if (!in_array($action, ['increase', 'decrease'], true)) {
        jsonError('Invalid action');
    }

    // Verify trip exists and user has permission
    $stmt = $pdo->prepare("SELECT created_by, budget, name FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trip) {
        jsonError('Trip not found');
    }

    $isCreator = isTripCreator($pdo, $tripId, $userId);
    if (!$isCreator && !$isMasterAdmin) {
        jsonError('Only trip creator or master admin can adjust budget');
    }

    // Calculate new budget
    $currentBudget = round(floatval($trip['budget'] ?? 0), 2);
    $newBudget = $action === 'increase' ? $currentBudget + $amount : $currentBudget - $amount;

    if ($newBudget < 0) {
        jsonError('Budget cannot be negative');
    }

    $pdo->beginTransaction();
    try {
        // Update trip budget
        $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ?");
        $stmt->execute([$newBudget, $tripId]);

        // Ensure Budget category exists
        ensureBudgetCategory($pdo);

        // Create budget adjustment expense record
        $subcategory = $action === 'increase' ? 'Budget Increase' : 'Budget Decrease';
        $description = $action === 'increase' ?
            "Budget increased by $" . number_format($amount, 2) :
            "Budget decreased by $" . number_format($amount, 2);

        createBudgetExpense($pdo, $tripId, $subcategory, $amount, $description, $userId);

        $pdo->commit();
        jsonResponse(true, [
            'message' => "Budget {$action}d by $" . number_format($amount, 2) . " successfully",
            'new_budget' => $newBudget
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Failed to adjust budget: ' . $e->getMessage());
    }
} catch (Exception $e) {
    jsonError('Error: ' . $e->getMessage());
}
?>