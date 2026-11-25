<?php
require_once '../includes/auth.php';
requireLogin();

$name = $_GET['name'] ?? '';
if (!is_string($name) || !preg_match('/\A(?:[a-f0-9]{32}|[a-f0-9]+_[0-9]+)\.(jpg|jpeg|png|gif|pdf|doc|docx)\z/D', $name, $parts)) {
    http_response_code(404);
    exit;
}
$stmt = $pdo->prepare('SELECT trip_id FROM chat_messages WHERE file_url = ? LIMIT 1');
$stmt->execute(['/uploads/chat/' . $name]);
$tripId = $stmt->fetchColumn();
if (!$tripId) {
    http_response_code(404);
    exit;
}
requireTripAccess($tripId);

$path = dirname(__DIR__) . '/uploads/chat/' . $name;
if (is_link($path) || !is_file($path) || !is_readable($path)) {
    http_response_code(404);
    exit;
}
$images = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
$mime = $images[$parts[1]] ?? 'application/octet-stream';
header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Length: ' . filesize($path));
header('Content-Disposition: ' . (isset($images[$parts[1]]) ? 'inline' : 'attachment') . '; filename="' . $name . '"');
session_write_close();
readfile($path);
