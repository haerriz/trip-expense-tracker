<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['categories' => $categories]);
?>