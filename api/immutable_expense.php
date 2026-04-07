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

function validateDate(string $date): bool {
    if ($date === '') {
        return false;
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return false;
    }

    return $timestamp <= time();
}

function isTripMember(PDO $pdo, $tripId, $userId): bool {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ? AND (status = 'accepted' OR status IS NULL)");
        $stmt->execute([$tripId, $userId]);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ? AND user_id = ?");
        $stmt->execute([$tripId, $userId]);
    }

    return $stmt->fetchColumn() > 0;
}

function getTripMemberIds(PDO $pdo, $tripId): array {
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM trip_members WHERE trip_id = ? AND (status = 'accepted' OR status IS NULL)");
        $stmt->execute([$tripId]);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT user_id FROM trip_members WHERE trip_id = ?");
        $stmt->execute([$tripId]);
    }

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function buildEqualSplits(array $memberIds, float $amount): array {
    $count = count($memberIds);
    if ($count === 0) {
        return [];
    }

    $base = floor(($amount * 100) / $count) / 100;
    $remainder = round($amount - ($base * $count), 2);
    $splits = [];

    foreach ($memberIds as $index => $memberId) {
        $splitAmount = $base;
        if ($index === 0) {
            $splitAmount = round($splitAmount + $remainder, 2);
        }
        $splits[$memberId] = $splitAmount;
    }

    return $splits;
}

function parseCustomSplits(array $memberIds, float $amount): array {
    $validMemberIds = array_flip($memberIds);
    $splits = [];
    $total = 0.0;

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'split_') !== 0) {
            continue;
        }

        $memberId = intval(substr($key, 6));
        if (!isset($validMemberIds[$memberId])) {
            continue;
        }

        $splitAmount = round(floatval($value), 2);
        if ($splitAmount <= 0) {
            continue;
        }

        $splits[$memberId] = $splitAmount;
        $total += $splitAmount;
    }

    if (empty($splits)) {
        return [];
    }

    if (abs($total - $amount) > 0.01) {
        return [];
    }

    return $splits;
}

function insertExpenseSplits(PDO $pdo, $expenseId, array $splits): void {
    $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");

    foreach ($splits as $memberId => $amount) {
        $stmt->execute([$expenseId, $memberId, $amount]);
    }
}

function handleExpenseSplits($expenseId, $tripId, float $amount, string $splitType, $paidBy): void {
    global $pdo;

    $memberIds = getTripMemberIds($pdo, $tripId);

    if ($splitType === 'equal') {
        if (empty($memberIds)) {
            throw new Exception('No trip members available for equal split');
        }

        $splits = buildEqualSplits($memberIds, $amount);
        insertExpenseSplits($pdo, $expenseId, $splits);
    } elseif ($splitType === 'full') {
        insertExpenseSplits($pdo, $expenseId, [$paidBy => round($amount, 2)]);
    } elseif ($splitType === 'custom') {
        $splits = parseCustomSplits($memberIds, $amount);
        if (empty($splits)) {
            throw new Exception('Custom split values are invalid or do not equal the total amount');
        }

        insertExpenseSplits($pdo, $expenseId, $splits);
    } else {
        throw new Exception('Invalid split type');
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonError('Invalid request method');
    }

    $action = trim($_POST['action'] ?? '');
    switch ($action) {
        case 'add':
            addExpense();
            break;
        case 'modify':
            modifyExpense();
            break;
        case 'deactivate':
            deactivateExpense();
            break;
        default:
            jsonError('Invalid action');
    }
} catch (Exception $e) {
    jsonError('Error: ' . $e->getMessage());
}

function addExpense(): void {
    global $pdo;

    $tripId = trim($_POST['trip_id'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $subcategory = trim($_POST['subcategory'] ?? '');
    $amount = round(floatval($_POST['amount'] ?? 0), 2);
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $splitType = trim($_POST['split_type'] ?? 'equal');
    $paidBy = trim($_POST['paid_by'] ?? $_SESSION['user_id']);
    $userId = $_SESSION['user_id'];

    if ($tripId === '' || $category === '' || $subcategory === '' || $description === '' || $date === '') {
        jsonError('Required fields are missing');
    }

    if ($amount <= 0) {
        jsonError('Amount must be greater than zero');
    }

    if (!validateDate($date)) {
        jsonError('Expense date is required and cannot be in the future');
    }

    if (!isTripMember($pdo, $tripId, $userId)) {
        jsonError('You are not a member of this trip');
    }

    if (!isTripMember($pdo, $tripId, $paidBy)) {
        jsonError('The selected payer is not a member of this trip');
    }

    if (!in_array($splitType, ['equal', 'full', 'custom'], true)) {
        jsonError('Invalid split type');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO expenses (trip_id, category, subcategory, amount, description, date, paid_by, split_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tripId, $category, $subcategory, $amount, $description, $date, $paidBy, $splitType]);
        $expenseId = $pdo->lastInsertId();

        handleExpenseSplits($expenseId, $tripId, $amount, $splitType, $paidBy);

        $pdo->commit();
        jsonResponse(true, ['expense_id' => $expenseId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Failed to add expense: ' . $e->getMessage());
    }
}

function modifyExpense(): void {
    global $pdo;

    $expenseId = trim($_POST['expense_id'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $subcategory = trim($_POST['subcategory'] ?? '');
    $amount = round(floatval($_POST['amount'] ?? 0), 2);
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $paidBy = trim($_POST['paid_by'] ?? '');
    $userId = $_SESSION['user_id'];
    $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';

    if ($expenseId === '' || $category === '' || $subcategory === '' || $description === '' || $date === '' || $paidBy === '') {
        jsonError('Required fields are missing');
    }

    if ($amount <= 0) {
        jsonError('Amount must be greater than zero');
    }

    if (!validateDate($date)) {
        jsonError('Expense date is required and cannot be in the future');
    }

    $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->execute([$expenseId]);
    $originalExpense = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$originalExpense) {
        jsonError('Expense not found');
    }

    if ($originalExpense['paid_by'] != $userId && !$isMasterAdmin) {
        jsonError('Only expense owner or master admin can modify this expense');
    }

    if (!isTripMember($pdo, $originalExpense['trip_id'], $paidBy)) {
        jsonError('The selected payer is not a member of this trip');
    }

    $splitType = trim($originalExpense['split_type'] ?? 'full');
    if (!in_array($splitType, ['equal', 'full', 'custom'], true)) {
        $splitType = 'full';
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE expenses SET category = ?, subcategory = ?, amount = ?, description = ?, date = ?, paid_by = ? WHERE id = ?");
        $stmt->execute([$category, $subcategory, $amount, $description, $date, $paidBy, $expenseId]);

        $stmt = $pdo->prepare("DELETE FROM expense_splits WHERE expense_id = ?");
        $stmt->execute([$expenseId]);

        handleExpenseSplits($expenseId, $originalExpense['trip_id'], $amount, $splitType, $paidBy);

        $pdo->commit();
        jsonResponse(true, ['message' => 'Expense updated successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Failed to modify expense: ' . $e->getMessage());
    }
}

function deactivateExpense(): void {
    global $pdo;

    $expenseId = trim($_POST['expense_id'] ?? '');
    $userId = $_SESSION['user_id'];
    $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';

    if ($expenseId === '') {
        jsonError('Expense ID required');
    }

    $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->execute([$expenseId]);
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expense) {
        jsonError('Expense not found');
    }

    if ($expense['paid_by'] != $userId && !$isMasterAdmin) {
        jsonError('Only expense owner or master admin can delete this expense');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("DELETE FROM expense_splits WHERE expense_id = ?");
        $stmt->execute([$expenseId]);

        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->execute([$expenseId]);

        $pdo->commit();
        jsonResponse(true, ['message' => 'Expense deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Failed to delete expense: ' . $e->getMessage());
    }
}
?>