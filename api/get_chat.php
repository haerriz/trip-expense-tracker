<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$tripId = $_GET['trip_id'];

$stmt = $pdo->prepare("
    SELECT tc.*, u.name as sender_name 
    FROM trip_chat tc 
    JOIN users u ON tc.user_id = u.id 
    WHERE tc.trip_id = ? 
    ORDER BY tc.created_at ASC 
    LIMIT 50
");
$stmt->execute([$tripId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['messages' => $messages]);
?>