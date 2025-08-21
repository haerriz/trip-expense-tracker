<?php
require_once 'includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Haerriz Trip Finance</title>
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="dashboard-page">
    <nav class="navbar">
        <div class="nav-wrapper">
            <a href="dashboard.php" class="navbar__brand brand-logo">
                <i class="material-icons">flight_takeoff</i>
                Trip Finance
            </a>
            <ul class="navbar__menu right">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php" class="btn-small red">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col s12 m8 offset-m2 l6 offset-l3">
                <div class="card profile-card">
                    <div class="card-content">
                        <span class="card-title">
                            <i class="material-icons left">person</i>Edit Profile
                        </span>
                        
                        <form id="profileForm" class="profile-form">
                            <div class="profile-avatar center-align">
                                <img id="profile-picture" src="<?php echo $_SESSION['user_picture'] ?: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjNjY3ZWVhIi8+Cjx0ZXh0IHg9IjUwIiB5PSI1NSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjMwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+VTwvdGV4dD4KPHN2Zz4='; ?>" alt="Profile" class="circle profile-avatar__image">
                                <p class="profile-avatar__text">Click to change avatar</p>
                                <input type="file" id="avatar-upload" accept="image/*" style="display:none;">
                            </div>
                            
                            <div class="input-field">
                                <input type="text" id="profileName" class="validate" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                                <label for="profileName" class="active">Full Name</label>
                            </div>
                            
                            <div class="input-field">
                                <input type="email" id="profileEmail" class="validate" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required>
                                <label for="profileEmail" class="active">Email</label>
                            </div>
                            
                            <div class="input-field">
                                <input type="tel" id="profilePhone" class="validate" value="">
                                <label for="profilePhone">Phone Number</label>
                                <span class="helper-text">
                                    <span id="phone-status" class="red-text"></span>
                                    <a href="#" id="verify-phone" class="blue-text" style="display:none;">Verify Phone (Demo)</a>
                                    <br><small class="grey-text">Demo: OTP will be shown on screen for testing</small>
                                </span>
                            </div>
                            
                            <div class="input-field">
                                <input type="password" id="currentPassword" class="validate">
                                <label for="currentPassword">Current Password (leave blank to keep unchanged)</label>
                            </div>
                            
                            <div class="input-field">
                                <input type="password" id="newPassword" class="validate">
                                <label for="newPassword">New Password</label>
                            </div>
                            
                            <div class="input-field">
                                <input type="password" id="confirmPassword" class="validate">
                                <label for="confirmPassword">Confirm New Password</label>
                            </div>
                            
                            <button type="submit" class="btn waves-effect waves-light profile-form__submit">
                                <i class="material-icons left">save</i>Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Verification Modal -->
    <div id="otp-modal" class="modal">
        <div class="modal-content">
            <h4>Verify Phone Number (Demo)</h4>
            <p>Demo Mode: The OTP is automatically filled below</p>
            <div class="card-panel orange lighten-4">
                <i class="material-icons left">info</i>
                <strong>Demo Notice:</strong> In production, OTP would be sent via SMS. For testing, it's shown in the toast notification and auto-filled.
            </div>
            <div class="input-field">
                <input type="text" id="otp-code" maxlength="6" class="validate">
                <label for="otp-code">6-digit OTP</label>
            </div>
            <div class="otp-timer">
                <span id="timer-text">Resend OTP in <span id="timer">60</span>s</span>
                <a href="#" id="resend-otp" style="display:none;">Resend OTP</a>
            </div>
        </div>
        <div class="modal-footer">
            <button id="verify-otp" class="btn waves-effect waves-light">Verify</button>
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancel</a>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="js/profile.js"></script>
</body>
</html>