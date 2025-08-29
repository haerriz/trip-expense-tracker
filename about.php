<?php
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Smart Trip Expense Tracking | Haerriz Trip Finance</title>
    <meta name="description" content="Learn about Haerriz Trip Finance - the smart expense tracking solution for backpackers and group travelers. Multi-currency support, real-time collaboration, and professional reporting.">
    <meta name="keywords" content="about haerriz, trip expense tracker, travel finance, expense splitting, group travel, backpacker expenses">
    <meta name="author" content="Haerriz">
    <meta name="theme-color" content="#2196F3">
    <link rel="canonical" href="https://expenses.haerriz.com/about.php">
    
    <!-- Open Graph -->
    <meta property="og:title" content="About Haerriz Trip Finance - Smart Expense Tracking">
    <meta property="og:description" content="Smart expense tracking solution for backpackers and group travelers with multi-currency support and real-time collaboration.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://expenses.haerriz.com/about.php">
    <meta property="og:site_name" content="Haerriz Trip Finance">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="About Haerriz Trip Finance">
    <meta name="twitter:description" content="Smart expense tracking for travelers">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "AboutPage",
      "name": "About Haerriz Trip Finance",
      "description": "Learn about our smart expense tracking solution for travelers",
      "url": "https://expenses.haerriz.com/about.php",
      "mainEntity": {
        "@type": "Organization",
        "name": "Haerriz",
        "url": "https://expenses.haerriz.com",
        "description": "Smart trip expense tracking platform for backpackers and group travelers",
        "foundingDate": "2024",
        "founder": {
          "@type": "Person",
          "name": "Haerriz"
        }
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
                <li><a href="index.php" class="btn white blue-text">Login</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <h1 class="card-title blue-text">About Haerriz Trip Finance</h1>
                        
                        <div class="row">
                            <div class="col s12 m8">
                                <h2>Smart Expense Tracking for Travelers</h2>
                                <p class="flow-text">
                                    Haerriz Trip Finance is designed specifically for backpackers and group travelers who need 
                                    a simple yet powerful way to track expenses, split costs, and manage trip budgets in real-time.
                                </p>
                                
                                <h3>Why We Built This</h3>
                                <p>
                                    After countless trips where expense tracking became a nightmare, we created a solution that 
                                    actually works for travelers. No more spreadsheets, no more arguments about who paid what.
                                </p>
                                
                                <h3>Key Features</h3>
                                <ul class="collection">
                                    <li class="collection-item">
                                        <i class="material-icons blue-text">currency_exchange</i>
                                        <strong>Multi-Currency Support</strong> - Track expenses in 9 major currencies
                                    </li>
                                    <li class="collection-item">
                                        <i class="material-icons blue-text">group</i>
                                        <strong>Group Expense Splitting</strong> - Automatically split costs among trip members
                                    </li>
                                    <li class="collection-item">
                                        <i class="material-icons blue-text">chat</i>
                                        <strong>Real-time Collaboration</strong> - Chat and coordinate with your travel group
                                    </li>
                                    <li class="collection-item">
                                        <i class="material-icons blue-text">analytics</i>
                                        <strong>Professional Reports</strong> - Export to PDF, Excel, or email summaries
                                    </li>
                                    <li class="collection-item">
                                        <i class="material-icons blue-text">mobile_friendly</i>
                                        <strong>Mobile-First Design</strong> - Works perfectly on your phone while traveling
                                    </li>
                                </ul>
                                
                                <h3>Built for Travelers, by Travelers</h3>
                                <p>
                                    Every feature is designed with real travel scenarios in mind. From hostel stays to group dinners, 
                                    from transportation costs to activity expenses - we've got you covered.
                                </p>
                            </div>
                            
                            <div class="col s12 m4">
                                <div class="card blue lighten-5">
                                    <div class="card-content">
                                        <h4 class="blue-text">Get Started</h4>
                                        <p>Ready to simplify your trip expenses?</p>
                                        <a href="index.php" class="btn blue waves-effect waves-light">
                                            Start Tracking
                                            <i class="material-icons right">arrow_forward</i>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-content">
                                        <h5>Contact</h5>
                                        <p>
                                            <i class="material-icons tiny">email</i>
                                            <a href="mailto:support@haerriz.com">support@haerriz.com</a>
                                        </p>
                                        <p>
                                            <i class="material-icons tiny">web</i>
                                            <a href="https://expenses.haerriz.com">expenses.haerriz.com</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                        <a href="index.php" class="white-text">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="js/dark-mode.js"></script>
</body>
</html>