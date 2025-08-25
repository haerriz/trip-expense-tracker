<?php
require_once 'includes/auth.php';
logout();

// Clear any cached data
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Force redirect to login page
header('Location: index.php?logout=1');
exit();
?>