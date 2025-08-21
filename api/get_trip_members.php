<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$tripId = $_GET['trip_id'];

$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, u.picture 
    FROM users u 
    JOIN trip_members tm ON u.id = tm.user_id 
    WHERE tm.trip_id = ?
    ORDER BY u.name
");
$stmt->execute([$tripId]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['members' => $members]);
?>