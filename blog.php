<?php
require_once 'config/seo-meta.php';
require_once 'config/schema-markup.php';

// Set page meta data
$pageMeta = getSEOMeta('blog');
$currentUrl = 'https://expenses.haerriz.com/blog.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php outputMetaTags('blog'); ?>

    <!-- Schema Markup -->
    <script type="application/ld+json">
    <?php
    $blogSchemas = [
        'website' => getWebSiteSchema(),
        'organization' => getOrganizationSchema(),
        'blog' => [
            "@context" => "https://schema.org",
            "@type" => "Blog",
            "name" => "Haerriz Travel Finance Blog",
            "description" => "Expert advice on travel budgeting, expense tracking, and money management for backpackers and group travelers",
            "url" => "https://expenses.haerriz.com/blog.php",
            "publisher" => [
                "@type" => "Organization",
                "name" => "Haerriz",
                "logo" => "https://expenses.haerriz.com/assets/logo.png"
            ]
        ]
    ];
    echo json_encode($blogSchemas, JSON_UNESCAPED_SLASHES);
    ?>
    </script>

    <!-- Preload critical resources -->
    <link rel="preload" href="css/style.css" as="style">
    <link rel="preload" href="js/blog.js" as="script">

    <!-- Styles -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0CMW9MRBRE"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-0CMW9MRBRE');
    </script>
</head>
<body>
    <!-- Skip to main content -->
    <a href="#main-content" class="skip-link" style="display:none;">Skip to main content</a>

    <!-- Navigation -->
    <nav class="nav-extended blue darken-1">
        <div class="nav-wrapper container">
            <a href="index.php" class="brand-logo">
                <i class="material-icons">flight_takeoff</i>
                Haerriz Trip Finance
            </a>
            <a href="#" data-target="mobile-nav" class="sidenav-trigger"><i class="material-icons">menu</i></a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li><a href="index.php">Home</a></li>
                <li><a href="features.php">Features</a></li>
                <li><a href="blog.php" class="active">Blog</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Mobile Navigation -->
    <ul class="sidenav" id="mobile-nav">
        <li><a href="index.php">Home</a></li>
        <li><a href="features.php">Features</a></li>
        <li><a href="blog.php" class="active">Blog</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="login.php">Login</a></li>
    </ul>

    <!-- Main Content -->
    <main id="main-content" class="container" role="main">
        <div class="row">
            <div class="col s12">
                <h1 class="center-align">Travel Finance Blog</h1>
                <p class="flow-text center-align grey-text text-darken-2">
                    Expert advice on travel budgeting, expense tracking, and money management for backpackers and group travelers
                </p>
            </div>
        </div>

        <!-- Featured Post -->
        <div class="row">
            <div class="col s12">
                <div class="card large hoverable">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&h=400&fit=crop" alt="Group of backpackers planning their trip budget">
                        <span class="card-title">How to Split Expenses on Group Trips: The Ultimate Guide</span>
                    </div>
                    <div class="card-content">
                        <p class="grey-text text-darken-1">Published April 7, 2026 • 8 min read</p>
                        <p>Traveling with friends or family can be amazing, but money disputes can ruin the fun. Learn how to split expenses fairly and avoid awkward conversations about who owes what.</p>
                        <div class="card-action">
                            <a href="#post-1" class="btn blue darken-1 waves-effect waves-light">Read More</a>
                            <span class="right">
                                <i class="material-icons tiny">visibility</i> 2.3K views
                                <i class="material-icons tiny">thumb_up</i> 156 likes
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blog Posts Grid -->
        <div class="row">
            <!-- Post 1 -->
            <div class="col s12 m6 l4">
                <div class="card medium hoverable">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=400&h=250&fit=crop" alt="Digital expense tracking on smartphone">
                        <span class="card-title">Best Expense Tracking Apps for 2026</span>
                    </div>
                    <div class="card-content">
                        <p>Discover the top expense tracking apps that can help you manage your travel budget effectively in 2026.</p>
                        <div class="card-action">
                            <a href="#post-2" class="btn-small blue darken-1 waves-effect waves-light">Read More</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post 2 -->
            <div class="col s12 m6 l4">
                <div class="card medium hoverable">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400&h=250&fit=crop" alt="Multi-currency travel wallet">
                        <span class="card-title">Multi-Currency Travel: Avoid Hidden Fees</span>
                    </div>
                    <div class="card-content">
                        <p>Learn how to manage multiple currencies while traveling and avoid expensive conversion fees.</p>
                        <div class="card-action">
                            <a href="#post-3" class="btn-small blue darken-1 waves-effect waves-light">Read More</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post 3 -->
            <div class="col s12 m6 l4">
                <div class="card medium hoverable">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=400&h=250&fit=crop" alt="Backpacker budgeting on laptop">
                        <span class="card-title">Budget Planning for Long-Term Backpackers</span>
                    </div>
                    <div class="card-content">
                        <p>Essential budgeting strategies for backpackers planning extended travel adventures.</p>
                        <div class="card-action">
                            <a href="#post-4" class="btn-small blue darken-1 waves-effect waves-light">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter Signup -->
        <div class="row">
            <div class="col s12">
                <div class="card-panel blue lighten-5">
                    <div class="row">
                        <div class="col s12 m8">
                            <h5>Stay Updated with Travel Finance Tips</h5>
                            <p>Get weekly tips on travel budgeting, expense tracking, and money-saving strategies delivered to your inbox.</p>
                        </div>
                        <div class="col s12 m4">
                            <form class="newsletter-form">
                                <div class="input-field">
                                    <input type="email" id="newsletter-email" class="validate" required>
                                    <label for="newsletter-email">Email Address</label>
                                </div>
                                <button class="btn blue darken-1 waves-effect waves-light" type="submit">
                                    Subscribe
                                    <i class="material-icons right">send</i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Blog Posts (Initially Hidden) -->
        <div id="blog-posts" style="display: none;">
            <!-- Post 1: How to Split Expenses -->
            <article id="post-1" class="blog-post">
                <h2>How to Split Expenses on Group Trips: The Ultimate Guide</h2>
                <div class="post-meta">
                    <span class="author">By Haerriz</span> |
                    <span class="date">April 7, 2026</span> |
                    <span class="reading-time">8 min read</span>
                </div>

                <div class="post-content">
                    <p>Traveling with friends or family can be amazing, but money disputes can ruin the fun. According to a recent survey, 73% of group travelers have experienced money-related conflicts during trips. The key to avoiding these issues is having a clear expense-splitting strategy from the beginning.</p>

                    <h3>1. Set Clear Expectations Before You Go</h3>
                    <p>Before your trip even starts, have an open conversation about money. Discuss:</p>
                    <ul>
                        <li>What expenses will be split (accommodation, transportation, food)</li>
                        <li>How expenses will be divided (equally, by income, custom percentages)</li>
                        <li>What happens if someone overspends</li>
                        <li>How payments will be collected</li>
                    </ul>

                    <h3>2. Use Technology to Your Advantage</h3>
                    <p>Modern expense tracking apps make splitting costs effortless. With Haerriz Trip Finance, you can:</p>
                    <ul>
                        <li>Automatically split expenses among group members</li>
                        <li>Track who owes what in real-time</li>
                        <li>Set budget limits and get alerts</li>
                        <li>Use AI to suggest upcoming expenses</li>
                    </ul>

                    <h3>3. Choose the Right Splitting Method</h3>
                    <p>Different situations call for different approaches:</p>
                    <ul>
                        <li><strong>Equal Split:</strong> Best for friends with similar budgets</li>
                        <li><strong>Income-Based:</strong> Fairer for groups with varying financial situations</li>
                        <li><strong>Custom Split:</strong> For unique circumstances (someone paid for a special experience)</li>
                    </ul>

                    <h3>4. Track Everything Immediately</h3>
                    <p>Don't wait until the end of the trip to sort out expenses. Record costs as they happen to avoid disputes and maintain accurate records.</p>

                    <div class="card-panel blue lighten-4">
                        <h4>💡 Pro Tip</h4>
                        <p>Use our AI expense suggestions to anticipate costs and budget accordingly. The AI analyzes your spending patterns and suggests likely upcoming expenses based on your trip type and location.</p>
                    </div>
                </div>
            </article>

            <!-- Post 2: Best Apps -->
            <article id="post-2" class="blog-post">
                <h2>Best Expense Tracking Apps for 2026</h2>
                <div class="post-meta">
                    <span class="author">By Haerriz</span> |
                    <span class="date">April 5, 2026</span> |
                    <span class="reading-time">6 min read</span>
                </div>

                <div class="post-content">
                    <p>With the rise of digital nomads and frequent travelers, expense tracking apps have become essential tools for managing travel finances. Here are the top apps for 2026:</p>

                    <h3>1. Haerriz Trip Finance (Our Pick!)</h3>
                    <p><strong>Best for:</strong> Group travel and backpackers</p>
                    <ul>
                        <li>AI-powered expense suggestions</li>
                        <li>Multi-currency support (9 currencies)</li>
                        <li>Real-time group expense splitting</li>
                        <li>Receipt analysis with AI</li>
                        <li>Free with no premium features locked</li>
                    </ul>

                    <h3>2. Trail Wallet</h3>
                    <p><strong>Best for:</strong> Offline expense tracking</p>
                    <ul>
                        <li>Works without internet connection</li>
                        <li>Offline receipt scanning</li>
                        <li>Syncs when connected</li>
                    </ul>

                    <h3>3. Splitwise</h3>
                    <p><strong>Best for:</strong> Simple expense splitting</p>
                    <ul>
                        <li>Easy group expense management</li>
                        <li>Clean, simple interface</li>
                        <li>Integration with other apps</li>
                    </ul>
                </div>
            </article>

            <!-- Post 3: Multi-Currency -->
            <article id="post-3" class="blog-post">
                <h2>Multi-Currency Travel: Avoid Hidden Fees</h2>
                <div class="post-meta">
                    <span class="author">By Haerriz</span> |
                    <span class="date">April 3, 2026</span> |
                    <span class="reading-time">5 min read</span>
                </div>

                <div class="post-content">
                    <p>Managing multiple currencies while traveling can be tricky, but with the right approach, you can avoid expensive fees and keep more money in your pocket.</p>

                    <h3>Understanding Currency Conversion Fees</h3>
                    <p>When you use your card abroad, you might encounter:</p>
                    <ul>
                        <li>Foreign transaction fees (1-3%)</li>
                        <li>Currency conversion fees (1-2%)</li>
                        <li>ATM withdrawal fees ($5-10)</li>
                        <li>Dynamic currency conversion (often 5-10% extra)</li>
                    </ul>

                    <h3>Best Practices for 2026</h3>
                    <ul>
                        <li>Use cards with no foreign transaction fees</li>
                        <li>Withdraw larger amounts less frequently</li>
                        <li>Use local cards when possible</li>
                        <li>Track all conversions in expense apps</li>
                        <li>Avoid dynamic currency conversion at merchants</li>
                    </ul>
                </div>
            </article>

            <!-- Post 4: Backpacker Budgeting -->
            <article id="post-4" class="blog-post">
                <h2>Budget Planning for Long-Term Backpackers</h2>
                <div class="post-meta">
                    <span class="author">By Haerriz</span> |
                    <span class="date">April 1, 2026</span> |
                    <span class="reading-time">7 min read</span>
                </div>

                <div class="post-content">
                    <p>Planning a long-term backpacking trip requires careful budgeting. Here's how to create a realistic budget for extended travel.</p>

                    <h3>Calculate Your Daily Budget</h3>
                    <p>Start by determining how much you can spend per day. Consider:</p>
                    <ul>
                        <li>Destination cost of living</li>
                        <li>Your travel style (budget vs. comfort)</li>
                        <li>Emergency fund requirements</li>
                        <li>Income sources during travel</li>
                    </ul>

                    <h3>Essential Budget Categories</h3>
                    <ul>
                        <li><strong>Accommodation:</strong> 30-40% of daily budget</li>
                        <li><strong>Food:</strong> 25-35% of daily budget</li>
                        <li><strong>Transportation:</strong> 15-25% of daily budget</li>
                        <li><strong>Activities/Entertainment:</strong> 10-20% of daily budget</li>
                        <li><strong>Miscellaneous:</strong> 10-15% of daily budget</li>
                    </ul>
                </div>
            </article>
        </div>
    </main>

    <!-- Footer -->
    <footer class="page-footer blue darken-1">
        <div class="container">
            <div class="row">
                <div class="col l6 s12">
                    <h5 class="white-text">Haerriz Trip Finance</h5>
                    <p class="grey-text text-lighten-4">
                        Free AI-powered expense tracker for backpackers and group travelers.
                        Multi-currency support, real-time analytics, and intelligent budget management.
                    </p>
                </div>
                <div class="col l4 offset-l2 s12">
                    <h5 class="white-text">Links</h5>
                    <ul>
                        <li><a class="grey-text text-lighten-3" href="index.php">Home</a></li>
                        <li><a class="grey-text text-lighten-3" href="features.php">Features</a></li>
                        <li><a class="grey-text text-lighten-3" href="blog.php">Blog</a></li>
                        <li><a class="grey-text text-lighten-3" href="about.php">About</a></li>
                        <li><a class="grey-text text-lighten-3" href="privacy.php">Privacy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <div class="container">
                © 2026 Haerriz Trip Finance. Made with ❤️ for travelers.
                <a class="grey-text text-lighten-4 right" href="https://github.com/haerriz/trip-expense-tracker">View on GitHub</a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="js/blog.js"></script>

    <!-- Schema Markup for Blog Posts -->
    <script type="application/ld+json">
    <?php
    $blogPostSchemas = [
        [
            "@context" => "https://schema.org",
            "@type" => "BlogPosting",
            "headline" => "How to Split Expenses on Group Trips: The Ultimate Guide",
            "description" => "Traveling with friends or family can be amazing, but money disputes can ruin the fun. Learn how to split expenses fairly and avoid awkward conversations.",
            "author" => [
                "@type" => "Person",
                "name" => "Haerriz"
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "Haerriz"
            ],
            "datePublished" => "2026-04-07",
            "dateModified" => "2026-04-07",
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => "https://expenses.haerriz.com/blog.php#post-1"
            ],
            "image" => "https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&h=400&fit=crop"
        ]
    ];
    echo json_encode($blogPostSchemas, JSON_UNESCAPED_SLASHES);
    ?>
    </script>
</body>
</html>