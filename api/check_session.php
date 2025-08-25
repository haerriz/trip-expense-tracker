<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

echo json_encode([
    'logged_in' => $logged_in,
    'user_id' => $logged_in ? $_SESSION['user_id'] : null,
    'user_name' => $logged_in ? $_SESSION['user_name'] : null
]);
?>