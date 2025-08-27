<?php
require_once 'includes/auth.php';
logout();

// Clear any cached data
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Successful - Haerriz Trip Finance</title>
    <meta name="description" content="You have been successfully logged out of Haerriz Trip Finance. Thank you for using our trip expense tracking service.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#2196F3">
    <link rel="canonical" href="https://expenses.haerriz.com/logout.php">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Logout Successful",
      "description": "User logout confirmation page for Haerriz Trip Finance.",
      "url": "https://expenses.haerriz.com/logout.php",
      "isPartOf": {
        "@type": "WebSite",
        "name": "Haerriz Trip Finance",
        "url": "https://expenses.haerriz.com"
      }
    }
    </script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon.svg">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
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
    
    <script>
        // Auto redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'index.php?logout=1';
        }, 3000);
    </script>
</head>
<body class="auth-page grey lighten-4">
    <main role="main" class="auth-page__container">
        <section class="auth-card card z-depth-3">
            <div class="auth-card__content card-content">
                <header class="auth-card__header center-align">
                    <div class="auth-brand-logo">
                        <div class="brand-icon-wrapper">
                            <i class="material-icons brand-icon">check_circle</i>
                        </div>
                        <h1 class="auth-card__title">
                            <span class="brand-name">Logout Successful</span>
                        </h1>
                    </div>
                    <p class="auth-card__subtitle">
                        You have been <span class="highlight-text">safely logged out</span> of Haerriz Trip Finance
                    </p>
                </header>
                
                <div class="center-align" style="margin: 30px 0;">
                    <div class="preloader-wrapper small active">
                        <div class="spinner-layer spinner-blue-only">
                            <div class="circle-clipper left">
                                <div class="circle"></div>
                            </div>
                            <div class="gap-patch">
                                <div class="circle"></div>
                            </div>
                            <div class="circle-clipper right">
                                <div class="circle"></div>
                            </div>
                        </div>
                    </div>
                    <p style="margin-top: 15px; color: #666;">Redirecting to login page...</p>
                </div>
                
                <div class="center-align">
                    <a href="index.php" class="btn-large waves-effect waves-light gradient-btn">
                        <span>Login Again</span>
                        <i class="material-icons right">arrow_forward</i>
                    </a>
                </div>
                
                <div class="auth-footer center-align">
                    <p class="footer-text">
                        Thank you for using <a href="https://expenses.haerriz.com" class="footer-link">Haerriz Trip Finance</a>
                    </p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>