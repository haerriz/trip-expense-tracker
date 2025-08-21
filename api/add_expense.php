<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tripId = $_POST['trip_id'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $splitType = $_POST['split_type'];
    $userId = $_SESSION['user_id'];
    
    // Add expense
    $stmt = $pdo->prepare("INSERT INTO expenses (trip_id, paid_by, category, subcategory, amount, description, date, split_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$tripId, $userId, $category, $subcategory, $amount, $description, $date, $splitType])) {
        $expenseId = $pdo->lastInsertId();
        
        // Get trip members for splitting
        $stmt = $pdo->prepare("SELECT user_id FROM trip_members WHERE trip_id = ?");
        $stmt->execute([$tripId]);
        $members = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Split expense equally among members
        $splitAmount = $amount / count($members);
        
        foreach ($members as $memberId) {
            $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
            $stmt->execute([$expenseId, $memberId, $splitAmount]);
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>