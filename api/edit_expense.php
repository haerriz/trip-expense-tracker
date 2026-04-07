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

function getExpenseSplits(PDO $pdo, $expenseId): array {
    $stmt = $pdo->prepare("SELECT user_id, amount FROM expense_splits WHERE expense_id = ? ORDER BY id ASC");
    $stmt->execute([$expenseId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $splits = [];

    foreach ($rows as $row) {
        $splits[intval($row['user_id'])] = round(floatval($row['amount']), 2);
    }

    return $splits;
}

function insertExpenseSplits(PDO $pdo, $expenseId, array $splits): void {
    $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
    foreach ($splits as $memberId => $splitAmount) {
        $stmt->execute([$expenseId, $memberId, $splitAmount]);
    }
}

function scaleSplits(array $splits, float $ratio, float $amount): array {
    $scaled = [];
    $total = 0.0;
    $memberIds = array_keys($splits);

    foreach ($splits as $memberId => $splitAmount) {
        $scaledAmount = round($splitAmount * $ratio, 2);
        $scaled[$memberId] = $scaledAmount;
        $total += $scaledAmount;
    }

    $remainder = round($amount - $total, 2);
    if (!empty($memberIds)) {
        $scaled[$memberIds[0]] = round($scaled[$memberIds[0]] + $remainder, 2);
    }

    return $scaled;
}

function jsonValidateDate(string $date): bool {
    $timestamp = strtotime($date);
    return $timestamp !== false && $timestamp <= time();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $expenseId = trim($_GET['expense_id'] ?? '');
        $userId = $_SESSION['user_id'];
        $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';

        if ($expenseId === '') {
            jsonError('Expense ID required');
        }

        $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ?");
        $stmt->execute([$expenseId]);
        $expense = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$expense || ($expense['paid_by'] != $userId && !$isMasterAdmin)) {
            jsonError('Expense not found or access denied');
        }

        jsonResponse(true, ['expense' => $expense]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $expenseId = trim($_POST['expense_id'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $subcategory = trim($_POST['subcategory'] ?? '');
        $amount = round(floatval($_POST['amount'] ?? 0), 2);
        $description = trim($_POST['description'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $paidBy = trim($_POST['paid_by'] ?? '');
        $userId = $_SESSION['user_id'];
        $isMasterAdmin = $_SESSION['user_email'] === 'haerriz@gmail.com';

        if ($expenseId === '' || $category === '' || $subcategory === '' || $description === '' || $paidBy === '') {
            jsonError('All fields are required');
        }

        if ($amount <= 0) {
            jsonError('Amount must be positive');
        }

        if (!jsonValidateDate($date)) {
            jsonError('Expense date is required and cannot be in the future');
        }

        $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ?");
        $stmt->execute([$expenseId]);
        $expense = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$expense) {
            jsonError('Expense not found');
        }

        if ($expense['paid_by'] != $userId && !$isMasterAdmin) {
            jsonError('Expense not found or access denied');
        }

        if (!isTripMember($pdo, $expense['trip_id'], $paidBy)) {
            jsonError('The selected payer is not a member of this trip');
        }

        $splitType = trim($expense['split_type'] ?? 'full');
        $memberIds = getTripMemberIds($pdo, $expense['trip_id']);
        if (empty($memberIds)) {
            jsonError('No trip members available for split calculation');
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE expenses SET category = ?, subcategory = ?, amount = ?, description = ?, date = ?, paid_by = ? WHERE id = ?");
            $stmt->execute([$category, $subcategory, $amount, $description, $date, $paidBy, $expenseId]);

            $originalSplits = getExpenseSplits($pdo, $expenseId);

            $stmt = $pdo->prepare("DELETE FROM expense_splits WHERE expense_id = ?");
            $stmt->execute([$expenseId]);

            if ($splitType === 'equal') {
                $splits = buildEqualSplits($memberIds, $amount);
                insertExpenseSplits($pdo, $expenseId, $splits);
            } elseif ($splitType === 'full') {
                insertExpenseSplits($pdo, $expenseId, [$paidBy => $amount]);
            } else {
                if (empty($originalSplits) || $expense['amount'] <= 0) {
                    insertExpenseSplits($pdo, $expenseId, [$paidBy => $amount]);
                } else {
                    $ratio = $expense['amount'] > 0 ? ($amount / floatval($expense['amount'])) : 1;
                    $splits = scaleSplits($originalSplits, $ratio, $amount);
                    insertExpenseSplits($pdo, $expenseId, $splits);
                }
            }

            $pdo->commit();
            jsonResponse(true, ['message' => 'Expense updated successfully']);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonError('Failed to update expense: ' . $e->getMessage());
        }
    } else {
        jsonError('Invalid request method');
    }
} catch (Exception $e) {
    jsonError('Error: ' . $e->getMessage());
}
?>