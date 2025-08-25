<?php
http_response_code(401);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access - Haerriz Trip Finance</title>
    <meta name="description" content="You don't have permission to access this page. Please login to your Haerriz Trip Finance account.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="https://expenses.haerriz.com/unauthorized">
    
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon.svg">
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="grey lighten-4">
    <nav class="blue darken-1">
        <div class="nav-wrapper">
            <a href="index.php" class="brand-logo">
                <i class="material-icons left">flight_takeoff</i>Trip Finance
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row" style="margin-top: 50px;">
            <div class="col s12 m8 offset-m2 l6 offset-l3">
                <div class="card">
                    <div class="card-content center-align">
                        <i class="material-icons large orange-text">lock</i>
                        <h1 class="grey-text">401 - Unauthorized</h1>
                        <p class="grey-text">You don't have permission to access this page. Please login to continue.</p>
                        
                        <div style="margin-top: 30px;">
                            <a href="index.php" class="btn blue waves-effect waves-light">
                                <i class="material-icons left">login</i>Login
                            </a>
                            <a href="manual-signup.php" class="btn green waves-effect waves-light">
                                <i class="material-icons left">person_add</i>Sign Up
                            </a>
                        </div>
                        
                        <div class="divider" style="margin: 30px 0;"></div>
                        
                        <div class="card-panel orange lighten-4">
                            <i class="material-icons left">info</i>
                            <strong>Need Help?</strong> This page requires authentication. Please login with your account credentials.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>