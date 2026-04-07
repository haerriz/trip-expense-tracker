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
    $budget = trim($_POST['budget'] ?? '');
    $noBudget = $_POST['no_budget'] ?? false;
    $userId = $_SESSION['user_id'];
    $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';

    if ($tripId === '') {
        jsonError('Trip ID required');
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
        jsonError('Only trip creator or master admin can edit budget');
    }

    // Handle budget update
    if ($noBudget === 'true' || $noBudget === true) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE trips SET budget = NULL WHERE id = ?");
            $stmt->execute([$tripId]);

            $pdo->commit();
            jsonResponse(true, ['message' => 'Budget removed - trip now has no budget limit']);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonError('Failed to remove budget: ' . $e->getMessage());
        }
    } else {
        // Validate and set budget
        if ($budget === '') {
            jsonError('Budget amount is required');
        }

        $budgetAmount = round(floatval($budget), 2);
        if ($budgetAmount < 0) {
            jsonError('Budget cannot be negative');
        }

        $pdo->beginTransaction();
        try {
            // Update trip budget
            $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ?");
            $stmt->execute([$budgetAmount, $tripId]);

            // Ensure Budget category exists
            ensureBudgetCategory($pdo);

            // Calculate the difference from current budget
            $currentBudget = round(floatval($trip['budget'] ?? 0), 2);
            $difference = $budgetAmount - $currentBudget;

            if ($difference != 0) {
                $subcategory = $difference > 0 ? 'Budget Increase' : 'Budget Decrease';
                $description = $difference > 0 ?
                    "Budget increased by $" . number_format(abs($difference), 2) :
                    "Budget decreased by $" . number_format(abs($difference), 2);

                createBudgetExpense($pdo, $tripId, $subcategory, abs($difference), $description, $userId);
            }

            $pdo->commit();
            jsonResponse(true, [
                'message' => $budgetAmount > 0 ?
                    "Budget updated to $" . number_format($budgetAmount, 2) :
                    'Budget set to $0.00',
                'new_budget' => $budgetAmount
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonError('Failed to update budget: ' . $e->getMessage());
        }
    }
} catch (Exception $e) {
    jsonError('Error: ' . $e->getMessage());
}
?>