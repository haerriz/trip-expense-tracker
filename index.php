<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in, show message instead of redirect
    echo '<script>alert("You are already logged in!"); window.location.href="dashboard.php";</script>';
    exit();
}

// User is not logged in, show login page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haerriz Trip Finance - Expense Tracker for Backpackers & Groups</title>
    <meta name="description" content="Free trip expense tracker for backpackers and group travelers. Split costs, track budgets, manage multi-currency expenses with real-time analytics. Perfect for tours, backpacking, and group travel.">
    <meta name="keywords" content="trip expense tracker, backpacking expenses, group travel budget, multi-currency, expense splitting, travel finance, tour expenses, travel budget app, group expense manager, backpacker finance, travel cost tracker, trip budget planner">
    <meta name="author" content="Haerriz">
    <meta name="theme-color" content="#2196F3">
    <meta name="application-name" content="Haerriz Trip Finance">
    <meta name="msapplication-TileColor" content="#2196F3">
    <meta name="msapplication-config" content="/browserconfig.xml">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://expenses.haerriz.com/">
    <meta property="og:title" content="Haerriz Trip Finance - Free Expense Tracker for Backpackers & Group Travel">
    <meta property="og:description" content="Track expenses, split costs, and manage budgets for your trips. Multi-currency support, real-time analytics, and group collaboration. Perfect for backpackers and tour groups.">
    <meta property="og:image" content="https://expenses.haerriz.com/assets/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Haerriz Trip Finance">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://expenses.haerriz.com/">
    <meta name="twitter:title" content="Haerriz Trip Finance - Free Trip Expense Tracker">
    <meta name="twitter:description" content="Split expenses, track budgets, manage multi-currency costs for group travel and backpacking trips.">
    <meta name="twitter:image" content="https://expenses.haerriz.com/assets/twitter-image.jpg">
    <meta name="twitter:creator" content="@haerriz">
    <meta name="twitter:site" content="@haerriz">
    
    <!-- SEO -->
    <link rel="canonical" href="https://expenses.haerriz.com/">
    <link rel="alternate" hreflang="en" href="https://expenses.haerriz.com/">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebApplication",
      "name": "Haerriz Trip Finance",
      "description": "Free trip expense tracker for backpackers and group travelers with multi-currency support and expense splitting.",
      "url": "https://expenses.haerriz.com",
      "applicationCategory": "FinanceApplication",
      "operatingSystem": "Web",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
      },
      "author": {
        "@type": "Person",
        "name": "Haerriz"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "ratingCount": "150"
      }
    }
    </script>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0CMW9MRBRE"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-0CMW9MRBRE');
    </script>
    
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="auth-page grey lighten-4">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <header role="banner">
        <h1 class="visually-hidden">Haerriz Trip Finance - Free Trip Expense Tracker</h1>
    </header>
    
    <main role="main" id="main-content" class="auth-page__container">
        <section class="auth-card card" aria-labelledby="main-heading">
            <div class="auth-card__content card-content">
                <header class="auth-card__header">
                    <h1 id="main-heading" class="auth-card__title">Haerriz Trip Finance</h1>
                    <p class="auth-card__subtitle">Track your backpacking and tour expenses with friends</p>
                </header>
                
                <!-- Manual Login Form -->
                <section id="login-form" class="auth-form auth-form--active" aria-labelledby="login-heading">
                    <h2 id="login-heading" class="visually-hidden">Login to Your Account</h2>
                    <form id="loginForm" class="auth-form__form" role="form" aria-label="Login form">
                        <div class="input-field">
                            <input type="email" id="loginEmail" class="validate" required aria-describedby="email-help" autocomplete="email">
                            <label for="loginEmail">Email Address</label>
                            <span id="email-help" class="visually-hidden">Enter your registered email address</span>
                        </div>
                        <div class="input-field">
                            <input type="password" id="loginPassword" class="validate" required aria-describedby="password-help" autocomplete="current-password">
                            <label for="loginPassword">Password</label>
                            <span id="password-help" class="visually-hidden">Enter your account password</span>
                        </div>
                        <button type="submit" class="btn waves-effect waves-light auth-form__submit" aria-describedby="login-button-help">
                            Login
                            <i class="material-icons right" aria-hidden="true">send</i>
                        </button>
                        <span id="login-button-help" class="visually-hidden">Click to sign in to your account</span>
                    </form>
                    <p class="auth-form__switch">Don't have an account? <a href="#" onclick="showSignup()" aria-label="Switch to sign up form">Sign up</a></p>
                </section>
                
                <!-- Manual Signup Form -->
                <div id="signup-form" class="auth-form" style="display:none;">
                    <form id="signupForm" class="auth-form__form">
                        <div class="input-field">
                            <input type="text" id="signupName" class="validate" required>
                            <label for="signupName">Full Name</label>
                        </div>
                        <div class="input-field">
                            <input type="email" id="signupEmail" class="validate" required>
                            <label for="signupEmail">Email</label>
                        </div>
                        <div class="input-field">
                            <input type="tel" id="signupPhone" class="validate" required>
                            <label for="signupPhone">Phone Number</label>
                        </div>
                        <div class="input-field">
                            <input type="password" id="signupPassword" class="validate" required>
                            <label for="signupPassword">Password</label>
                        </div>
                        <div class="auth-form__captcha">
                            <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                        </div>
                        <button type="submit" class="btn waves-effect waves-light auth-form__submit">
                            Sign Up
                            <i class="material-icons right">person_add</i>
                        </button>
                    </form>
                    <p class="auth-form__switch">Already have an account? <a href="#" onclick="showLogin()">Login</a></p>
                </div>
                
                <div class="auth-card__divider">
                    <span>OR</span>
                </div>
                
                <!-- Google OAuth -->
                <div class="auth-card__oauth">
                    <div id="g_id_onload"
                         data-client_id="435239215784-eckha7a4i5fg8ik7u7f7h750nc2upibh.apps.googleusercontent.com"
                         data-context="signin"
                         data-ux_mode="popup"
                         data-callback="handleCredentialResponse"
                         data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin"
                         data-type="standard"
                         data-size="large"
                         data-theme="outline"
                         data-text="sign_in_with"
                         data-shape="rectangular"
                         data-logo_alignment="left">
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/manual-auth.js"></script>
    <script src="js/mobile-auth.js"></script>
</body>
</html>