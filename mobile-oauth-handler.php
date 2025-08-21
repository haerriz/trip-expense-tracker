<?php
session_start();
require_once 'config/database.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $clientId = '435239215784-eckha7a4i5fg8ik7u7f7h750nc2upibh.apps.googleusercontent.com';
    $clientSecret = 'YOUR_CLIENT_SECRET'; // You'll need to add this
    $redirectUri = 'https://expenses.haerriz.com/mobile-oauth-handler.php';
    
    // Exchange code for access token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $tokenData = [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirectUri
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $tokenResponse = curl_exec($ch);
    curl_close($ch);
    
    $tokenInfo = json_decode($tokenResponse, true);
    
    if (isset($tokenInfo['access_token'])) {
        // Get user info
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $tokenInfo['access_token'];
        $userInfo = json_decode(file_get_contents($userInfoUrl), true);
        
        if ($userInfo && isset($userInfo['email'])) {
            try {
                $email = $userInfo['email'];
                $name = $userInfo['name'] ?? '';
                $picture = $userInfo['picture'] ?? '';
                
                // Check if user exists
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Update existing user
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, picture = ? WHERE email = ?");
                    $stmt->execute([$name, $picture, $email]);
                    $userId = $user['id'];
                } else {
                    // Create new user
                    $stmt = $pdo->prepare("INSERT INTO users (email, name, picture, password) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$email, $name, $picture, 'google_oauth']);
                    $userId = $pdo->lastInsertId();
                }
                
                // Set session
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_picture'] = $picture;
                
                // Send success message to parent window
                echo '<script>
                    window.parent.postMessage({
                        type: "GOOGLE_AUTH_SUCCESS"
                    }, "https://expenses.haerriz.com");
                </script>';
                exit;
                
            } catch (Exception $e) {
                echo '<script>
                    window.parent.postMessage({
                        type: "GOOGLE_AUTH_ERROR",
                        message: "Database error"
                    }, "https://expenses.haerriz.com");
                </script>';
                exit;
            }
        }
    }
}

// Error case
echo '<script>
    window.parent.postMessage({
        type: "GOOGLE_AUTH_ERROR",
        message: "Authentication failed"
    }, "https://expenses.haerriz.com");
</script>';
?>