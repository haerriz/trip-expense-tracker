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
    <meta name="keywords" content="Haerriz trip expense tracker, backpacking expenses, group travel budget, multi-currency expense tracker, expense splitting app, travel finance management, tour expenses tracker, travel budget app, group expense manager, backpacker finance tool, travel cost tracker, trip budget planner, free expense tracker, travel expense sharing, group trip budget, vacation expense tracker, travel money management, expense split calculator, trip cost sharing, travel budget organizer">
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
      "alternateName": "Haerriz Expense Tracker",
      "description": "Free trip expense tracker for backpackers and group travelers with multi-currency support and expense splitting.",
      "url": "https://expenses.haerriz.com",
      "applicationCategory": "FinanceApplication",
      "operatingSystem": "Web",
      "browserRequirements": "Requires JavaScript. Requires HTML5.",
      "softwareVersion": "2.0",
      "releaseNotes": "Enhanced UI with Material Design, improved mobile experience, professional PDF/Excel exports",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock"
      },
      "author": {
        "@type": "Person",
        "name": "Haerriz",
        "url": "https://expenses.haerriz.com",
        "sameAs": [
          "https://github.com/haerriz",
          "https://twitter.com/haerriz"
        ]
      },
      "creator": {
        "@type": "Person",
        "name": "Haerriz"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "ratingCount": "150",
        "bestRating": "5",
        "worstRating": "1"
      },
      "featureList": [
        "Multi-currency expense tracking",
        "Group expense splitting",
        "Real-time chat collaboration",
        "Professional PDF/Excel reports",
        "Mobile-responsive design",
        "Google OAuth integration",
        "Budget tracking and analytics",
        "Expense categorization"
      ],
      "screenshot": "https://expenses.haerriz.com/assets/screenshot.jpg",
      "applicationSubCategory": "Travel Finance",
      "downloadUrl": "https://expenses.haerriz.com",
      "installUrl": "https://expenses.haerriz.com",
      "memoryRequirements": "512MB",
      "storageRequirements": "10MB",
      "permissions": "camera, location"
    }
    </script>
    
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Haerriz",
      "url": "https://expenses.haerriz.com",
      "logo": "https://expenses.haerriz.com/favicon.svg",
      "sameAs": [
        "https://github.com/haerriz",
        "https://twitter.com/haerriz"
      ],
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer service",
        "availableLanguage": "English"
      }
    }
    </script>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon.svg">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Enhanced SEO -->
    <meta name="google-site-verification" content="haerriz-trip-finance-verification">
    <meta name="msvalidate.01" content="haerriz-trip-finance-bing">
    <meta name="yandex-verification" content="haerriz-trip-finance-yandex">
    <link rel="sitemap" type="application/xml" href="/sitemap.xml">
    <meta name="rating" content="general">
    <meta name="distribution" content="global">
    <meta name="revisit-after" content="7 days">
    <meta name="language" content="en">
    <meta name="geo.region" content="US">
    <meta name="geo.placename" content="United States">
    
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
        <h1 class="visually-hidden" style="display:none;">Haerriz Trip Finance - Free Trip Expense Tracker</h1>
    </header>
    
    <main role="main" id="main-content" class="auth-page__container">
        <section class="auth-card card z-depth-3" aria-labelledby="main-heading">
            <div class="auth-card__content card-content">
                <!-- Modern Header with Brand Identity -->
                <header class="auth-card__header center-align">
                    <div class="auth-brand-logo">
                        <div class="brand-icon-wrapper">
                            <i class="material-icons brand-icon">flight_takeoff</i>
                        </div>
                        <h1 id="main-heading" class="auth-card__title">
                            <span class="brand-name">Haerriz</span>
                            <span class="brand-product">Trip Finance</span>
                        </h1>
                    </div>
                    <p class="auth-card__subtitle">
                        <span class="highlight-text">Smart expense tracking</span> for backpackers and group travelers
                    </p>
                    
                    <!-- Feature Highlights -->
                    <div class="feature-badges">
                        <span class="feature-badge">
                            <i class="material-icons tiny">groups</i> Group Splitting
                        </span>
                        <span class="feature-badge">
                            <i class="material-icons tiny">currency_exchange</i> Multi-Currency
                        </span>
                        <span class="feature-badge">
                            <i class="material-icons tiny">analytics</i> Real-time Analytics
                        </span>
                    </div>
                </header>
                
                <!-- Enhanced Login Form -->
                <section id="login-form" class="auth-form auth-form--active" aria-labelledby="login-heading">
                    <h2 id="login-heading" class="visually-hidden">Login to Your Account</h2>
                    
                    <div class="form-header center-align">
                        <h3 class="form-title">Welcome Back</h3>
                        <p class="form-subtitle">Sign in to manage your travel expenses</p>
                    </div>
                    
                    <form id="loginForm" class="auth-form__form modern-form" role="form" aria-label="Login form">
                        <div class="input-field modern-input">
                            <i class="material-icons prefix">email</i>
                            <input type="email" id="loginEmail" class="validate" required aria-describedby="email-help" autocomplete="email">
                            <label for="loginEmail">Email Address</label>
                            <span id="email-help" class="visually-hidden">Enter your registered email address</span>
                        </div>
                        <div class="input-field modern-input">
                            <i class="material-icons prefix">lock</i>
                            <input type="password" id="loginPassword" class="validate" required aria-describedby="password-help" autocomplete="current-password">
                            <label for="loginPassword">Password</label>
                            <span id="password-help" class="visually-hidden">Enter your account password</span>
                        </div>
                        
                        <button type="submit" class="btn-large waves-effect waves-light auth-form__submit gradient-btn" aria-describedby="login-button-help">
                            <span>Sign In</span>
                            <i class="material-icons right" aria-hidden="true">arrow_forward</i>
                        </button>
                        <span id="login-button-help" class="visually-hidden">Click to sign in to your account</span>
                    </form>
                    
                    <div class="auth-form__switch center-align">
                        <p>New to Haerriz? <a href="#" onclick="showSignup()" class="auth-link" aria-label="Switch to sign up form">Create Account</a></p>
                    </div>
                </section>
                
                <!-- Enhanced Signup Form -->
                <div id="signup-form" class="auth-form" style="display:none;">
                    <div class="form-header center-align">
                        <h3 class="form-title">Join Haerriz</h3>
                        <p class="form-subtitle">Start tracking your travel expenses today</p>
                    </div>
                    
                    <form id="signupForm" class="auth-form__form modern-form">
                        <div class="input-field modern-input">
                            <i class="material-icons prefix">person</i>
                            <input type="text" id="signupName" class="validate" required>
                            <label for="signupName">Full Name</label>
                        </div>
                        <div class="input-field modern-input">
                            <i class="material-icons prefix">email</i>
                            <input type="email" id="signupEmail" class="validate" required>
                            <label for="signupEmail">Email Address</label>
                        </div>
                        <div class="input-field modern-input">
                            <i class="material-icons prefix">phone</i>
                            <input type="tel" id="signupPhone" class="validate" required>
                            <label for="signupPhone">Phone Number</label>
                        </div>
                        <div class="input-field modern-input">
                            <i class="material-icons prefix">lock</i>
                            <input type="password" id="signupPassword" class="validate" required>
                            <label for="signupPassword">Create Password</label>
                        </div>
                        
                        <div class="auth-form__captcha center-align">
                            <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                        </div>
                        
                        <button type="submit" class="btn-large waves-effect waves-light auth-form__submit gradient-btn">
                            <span>Create Account</span>
                            <i class="material-icons right">person_add</i>
                        </button>
                    </form>
                    
                    <div class="auth-form__switch center-align">
                        <p>Already have an account? <a href="#" onclick="showLogin()" class="auth-link">Sign In</a></p>
                    </div>
                </div>
                
                <!-- Modern Divider -->
                <div class="auth-card__divider">
                    <div class="divider-line"></div>
                    <span class="divider-text">or continue with</span>
                    <div class="divider-line"></div>
                </div>
                
                <!-- Enhanced Google OAuth -->
                <div class="auth-card__oauth center-align">
                    <div class="oauth-wrapper">
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
                             data-text="continue_with"
                             data-shape="rectangular"
                             data-logo_alignment="left">
                        </div>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="trust-indicators">
                        <div class="trust-item">
                            <i class="material-icons tiny">security</i>
                            <span>Secure & Private</span>
                        </div>
                        <div class="trust-item">
                            <i class="material-icons tiny">cloud_done</i>
                            <span>Cloud Synced</span>
                        </div>
                        <div class="trust-item">
                            <i class="material-icons tiny">mobile_friendly</i>
                            <span>Mobile Ready</span>
                        </div>
                    </div>
                </div>
                
                <!-- SEO-Enhanced Footer -->
                <footer class="auth-footer center-align">
                    <p class="footer-text">
                        <strong>Free forever</strong> • No hidden fees • <a href="#privacy" class="footer-link">Privacy Policy</a>
                    </p>
                </footer>
            </div>
        </section>
    </main>
    
    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/manual-auth.js"></script>
    <script src="js/mobile-auth.js"></script>
    <script src="js/pwa-install.js"></script>
</body>
</html>