<?php
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features - Complete Trip Expense Management | Haerriz Trip Finance</title>
    <meta name="description" content="Discover all features of Haerriz Trip Finance: multi-currency tracking, group expense splitting, real-time chat, professional reports, budget management, and mobile-first design.">
    <meta name="keywords" content="trip expense features, multi-currency, expense splitting, travel budget, group expenses, expense reports, mobile expense tracker">
    <meta name="author" content="Haerriz">
    <meta name="theme-color" content="#2196F3">
    <link rel="canonical" href="https://expenses.haerriz.com/features.php">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Features - Haerriz Trip Finance">
    <meta property="og:description" content="Complete trip expense management with multi-currency support, group splitting, and professional reporting.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://expenses.haerriz.com/features.php">
    <meta property="og:site_name" content="Haerriz Trip Finance">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Features",
      "description": "Complete overview of Haerriz Trip Finance features for expense tracking and management",
      "url": "https://expenses.haerriz.com/features.php"
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
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0CMW9MRBRE"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-0CMW9MRBRE');
    </script>
</head>
<body class="grey lighten-4">
    <nav class="blue">
        <div class="nav-wrapper">
            <a href="index.php" class="brand-logo">
                <i class="material-icons left">flight_takeoff</i>Haerriz Trip Finance
            </a>
            <ul class="right">
                <li>
                    <button class="theme-toggle" title="Toggle dark mode">
                        <svg viewBox="0 0 24 24"><path d="M12.34 2.02C6.59 1.82 2 6.42 2 12c0 5.52 4.48 10 10 10 3.71 0 6.93-2.02 8.66-5.02-7.51-.25-13.64-6.42-13.64-13.96 0-.34.02-.67.05-1z"/></svg>
                    </button>
                </li>
                <li><a href="about.php">About</a></li>
                <li><a href="index.php" class="btn white blue-text">Login</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="row">
            <div class="col s12">
                <div class="center-align" style="margin: 40px 0;">
                    <h1 class="blue-text">Powerful Features for Smart Travelers</h1>
                    <p class="flow-text grey-text text-darken-1">Everything you need to manage trip expenses effortlessly</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col s12 m6 l4">
                <div class="card hoverable">
                    <div class="card-content center-align">
                        <i class="material-icons large blue-text">currency_exchange</i>
                        <h3>Multi-Currency Support</h3>
                        <p>Track expenses in 9 major currencies: USD, EUR, GBP, JPY, AUD, CAD, INR, THB, VND. Perfect for international travel.</p>
                    </div>
                </div>
            </div>
            
            <div class="col s12 m6 l4">
                <div class="card hoverable">
                    <div class="card-content center-align">
                        <i class="material-icons large green-text">group</i>
                        <h3>Group Expense Splitting</h3>
                        <p>Automatically split expenses among trip members. Equal splits, custom amounts, or percentage-based divisions.</p>
                    </div>
                </div>
            </div>
            
            <div class="col s12 m6 l4">
                <div class="card hoverable">
                    <div class="card-content center-align">
                        <i class="material-icons large orange-text">chat</i>
                        <h3>Real-time Chat</h3>
                        <p>Coordinate with your travel group in real-time. Discuss expenses, share updates, and stay connected.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col s12 m6 l4">
                <div class="card hoverable">
                    <div class="card-content center-align">
                        <i class="material-icons large red-text">analytics</i>
                        <h3>Professional Reports</h3>
                        <p>Export detailed reports to PDF, Excel, or email. Perfect for reimbursements and record keeping.</p>
                    </div>
                </div>
            </div>
            
            <div class="col s12 m6 l4">
                <div class="card hoverable">
                    <div class="card-content center-align">
                        <i class="material-icons large purple-text">account_balance_wallet</i>
                        <h3>Budget Management</h3>
                        <p>Set trip budgets, track spending in real-time, and get alerts when approaching limits.</p>
                    </div>
                </div>
            </div>
            
            <div class="col s12 m6 l4">
                <div class="card hoverable">
                    <div class="card-content center-align">
                        <i class="material-icons large teal-text">mobile_friendly</i>
                        <h3>Mobile-First Design</h3>
                        <p>Optimized for mobile use while traveling. Works offline and syncs when connected.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col s12">
                <div class="card blue lighten-5">
                    <div class="card-content">
                        <h2 class="blue-text">Detailed Categories</h2>
                        <p>Organize expenses with travel-specific categories:</p>
                        <div class="row">
                            <div class="col s12 m6">
                                <ul class="collection">
                                    <li class="collection-item">🍽️ Food & Drinks</li>
                                    <li class="collection-item">🚗 Transportation</li>
                                    <li class="collection-item">🏨 Accommodation</li>
                                    <li class="collection-item">🎯 Activities</li>
                                </ul>
                            </div>
                            <div class="col s12 m6">
                                <ul class="collection">
                                    <li class="collection-item">🛍️ Shopping</li>
                                    <li class="collection-item">🚨 Emergency</li>
                                    <li class="collection-item">💡 Other</li>
                                    <li class="collection-item">📊 Custom Categories</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col s12 center-align">
                <div class="card">
                    <div class="card-content">
                        <h2>Ready to Get Started?</h2>
                        <p class="flow-text">Join thousands of travelers who trust Haerriz Trip Finance</p>
                        <a href="index.php" class="btn-large blue waves-effect waves-light">
                            Start Your Free Trip
                            <i class="material-icons right">arrow_forward</i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="blue darken-1 white-text">
        <div class="container">
            <div class="row">
                <div class="col s12 center-align">
                    <p>&copy; 2024 Haerriz Trip Finance. Built for travelers, by travelers.</p>
                    <p>
                        <a href="privacy.php" class="white-text">Privacy Policy</a> | 
                        <a href="about.php" class="white-text">About</a> | 
                        <a href="features.php" class="white-text">Features</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="js/dark-mode.js"></script>
</body>
</html>