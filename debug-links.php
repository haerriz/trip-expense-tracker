<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Links Test</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body>
    <h1>Link Debug Test</h1>
    <p>Current URL: <?php echo $_SERVER['REQUEST_URI']; ?></p>
    <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    
    <h2>Test Links:</h2>
    <ul>
        <li><a href="https://expenses.haerriz.com/dashboard.php?t=<?php echo time(); ?>">Dashboard (with timestamp)</a></li>
        <li><a href="https://expenses.haerriz.com/admin.php?t=<?php echo time(); ?>">Admin (with timestamp)</a></li>
        <li><a href="https://expenses.haerriz.com/profile.php?t=<?php echo time(); ?>">Profile (with timestamp)</a></li>
        <li><a href="https://expenses.haerriz.com/logout.php?t=<?php echo time(); ?>">Logout (with timestamp)</a></li>
    </ul>
    
    <h2>JavaScript Test:</h2>
    <button onclick="showLinks()">Show All Link URLs</button>
    <div id="link-info"></div>
    
    <script>
    function showLinks() {
        const links = document.querySelectorAll('a');
        let info = '<h3>All Links Found:</h3><ul>';
        links.forEach((link, index) => {
            info += `<li>Link ${index + 1}: "${link.textContent}" → href="${link.href}"</li>`;
        });
        info += '</ul>';
        document.getElementById('link-info').innerHTML = info;
    }
    
    // Auto-run on page load
    window.onload = function() {
        setTimeout(showLinks, 1000);
    };
    </script>
</body>
</html>