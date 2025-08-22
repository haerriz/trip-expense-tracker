<?php
session_start();

echo "<h1>Redirect Test</h1>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>HTTP Host: " . $_SERVER['HTTP_HOST'] . "</p>";

if (isset($_GET['test'])) {
    echo "<p>Testing redirect to dashboard.php...</p>";
    header('Location: /dashboard.php');
    exit();
}

echo "<p><a href='?test=1'>Test Redirect to Dashboard.php</a></p>";
echo "<p><a href='/dashboard.php'>Direct Link to Dashboard.php</a></p>";
echo "<p><a href='/dashboard'>Direct Link to Dashboard (clean URL)</a></p>";
?>