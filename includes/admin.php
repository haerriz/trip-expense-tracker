<?php
function isMasterAdmin() {
    return isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'haerriz@gmail.com';
}

function requireMasterAdmin() {
    if (!isMasterAdmin()) {
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Master admin access required']);
            exit();
        } else {
            header('Location: /dashboard');
            exit();
        }
    }
}
?>