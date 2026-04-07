<?php
// Dynamic SEO meta tags generator for better search visibility

function getSEOMeta($page, $data = []) {
    $baseUrl = 'https://expenses.haerriz.com';

    $meta = [
        'index' => [
            'title' => 'Haerriz Trip Finance - Free AI-Powered Expense Tracker for Backpackers & Group Travel',
            'description' => 'Track group expenses, split costs, manage budgets with AI suggestions. Perfect for backpacking, tours, and group travel. Free multi-currency expense tracker with real-time analytics.',
            'keywords' => 'trip expense tracker, backpacking expenses, group travel budget, expense splitter, multi-currency tracker, AI expense suggestions, travel finance, tour budget, free expense tracker, travel cost sharing',
            'canonical' => $baseUrl,
            'image' => $baseUrl . '/assets/og-image.jpg',
            'type' => 'website'
        ],
        'dashboard' => [
            'title' => 'Dashboard - Haerriz Trip Finance | Manage Your Travel Expenses',
            'description' => 'Manage your trips, view real-time analytics, track expenses, and get AI-powered budget advice. Split costs with travel companions effortlessly.',
            'keywords' => 'travel dashboard, expense management, trip analytics, budget tracking, group expenses, travel finance dashboard',
            'canonical' => $baseUrl . '/dashboard.php',
            'image' => $baseUrl . '/assets/dashboard-og.jpg',
            'type' => 'website'
        ],
        'features' => [
            'title' => 'Features - Haerriz Trip Finance | AI Expense Tracker & Budget Management',
            'description' => 'Discover AI-powered expense suggestions, multi-currency support, group collaboration, real-time analytics, and receipt analysis. Everything you need for travel finance.',
            'keywords' => 'expense tracker features, AI travel finance, multi-currency support, group expense splitting, travel budget analytics, receipt scanner',
            'canonical' => $baseUrl . '/features.php',
            'image' => $baseUrl . '/assets/features-og.jpg',
            'type' => 'website'
        ],
        'about' => [
            'title' => 'About Haerriz - Making Travel Expense Tracking Easy & Smart',
            'description' => 'Learn how Haerriz helps backpackers and tour groups manage finances while traveling. AI-powered insights, multi-currency support, and seamless group collaboration.',
            'keywords' => 'about Haerriz, travel expense tracking, backpacker finance, group travel money management, AI travel assistant',
            'canonical' => $baseUrl . '/about.php',
            'image' => $baseUrl . '/assets/about-og.jpg',
            'type' => 'website'
        ],
        'login' => [
            'title' => 'Login - Haerriz Trip Finance | Secure Access to Your Travel Expenses',
            'description' => 'Sign in to access your trip expenses, budget tracking, and group finance management. Secure authentication with Google OAuth support.',
            'keywords' => 'login, sign in, travel expense tracker login, secure authentication, Google OAuth',
            'canonical' => $baseUrl . '/login.php',
            'type' => 'website'
        ],
        'signup' => [
            'title' => 'Sign Up - Haerriz Trip Finance | Free Travel Expense Tracker',
            'description' => 'Create your free account and start tracking travel expenses with AI assistance. Join thousands of backpackers and tour groups managing their finances.',
            'keywords' => 'sign up, register, free account, travel expense tracker, backpacker finance',
            'canonical' => $baseUrl . '/signup.php',
            'type' => 'website'
        ],
        'blog' => [
            'title' => 'Travel Finance Blog - Haerriz Trip Finance | Expert Travel Money Tips',
            'description' => 'Expert advice on travel budgeting, expense tracking, and money management for backpackers and group travelers. AI-powered insights and practical tips.',
            'keywords' => 'travel finance blog, backpacking budget tips, group travel expenses, travel money management, expense tracking advice',
            'canonical' => $baseUrl . '/blog.php',
            'image' => $baseUrl . '/assets/blog-og.jpg',
            'type' => 'website'
        ],
        'privacy' => [
            'title' => 'Privacy Policy - Haerriz Trip Finance | Data Protection & Security',
            'description' => 'Learn how we protect your travel expense data and personal information. Transparent privacy practices for secure expense tracking.',
            'keywords' => 'privacy policy, data protection, travel expense security, GDPR compliance, data privacy',
            'canonical' => $baseUrl . '/privacy.php',
            'type' => 'website'
        ]
    ];

    // Handle dynamic pages
    if ($page === 'trip' && isset($data['trip_name'])) {
        return [
            'title' => "{$data['trip_name']} - Expense Tracking | Haerriz Trip Finance",
            'description' => "Track expenses for {$data['trip_name']}. AI-powered budget management, group expense splitting, and real-time analytics for your trip.",
            'keywords' => 'trip expenses, travel budget, group finance, expense tracking, trip management',
            'canonical' => $baseUrl . '/trip.php?id=' . ($data['trip_id'] ?? ''),
            'image' => $baseUrl . '/assets/trip-og.jpg',
            'type' => 'article'
        ];
    }

    if ($page === 'blog-post' && isset($data['title'])) {
        return [
            'title' => $data['title'] . ' | Travel Finance Blog - Haerriz',
            'description' => $data['excerpt'] ?? substr($data['content'] ?? '', 0, 160),
            'keywords' => $data['keywords'] ?? 'travel finance, expense tracking, budget tips',
            'canonical' => $baseUrl . '/blog/' . ($data['slug'] ?? ''),
            'image' => $data['image'] ?? $baseUrl . '/assets/blog-default.jpg',
            'type' => 'article',
            'article' => [
                'published_time' => $data['published_time'] ?? date('c'),
                'modified_time' => $data['modified_time'] ?? date('c'),
                'author' => 'Haerriz',
                'section' => 'Travel Finance'
            ]
        ];
    }

    return $meta[$page] ?? $meta['index'];
}

function outputMetaTags($page, $data = []) {
    $meta = getSEOMeta($page, $data);

    // Basic meta tags
    echo '<title>' . htmlspecialchars($meta['title']) . '</title>' . "\n";
    echo '<meta name="description" content="' . htmlspecialchars($meta['description']) . '">' . "\n";
    echo '<meta name="keywords" content="' . htmlspecialchars($meta['keywords']) . '">' . "\n";
    echo '<link rel="canonical" href="' . htmlspecialchars($meta['canonical']) . '">' . "\n";

    // Open Graph tags
    echo '<meta property="og:title" content="' . htmlspecialchars($meta['title']) . '">' . "\n";
    echo '<meta property="og:description" content="' . htmlspecialchars($meta['description']) . '">' . "\n";
    echo '<meta property="og:image" content="' . htmlspecialchars($meta['image']) . '">' . "\n";
    echo '<meta property="og:url" content="' . htmlspecialchars($meta['canonical']) . '">' . "\n";
    echo '<meta property="og:type" content="' . htmlspecialchars($meta['type']) . '">' . "\n";
    echo '<meta property="og:site_name" content="Haerriz Trip Finance">' . "\n";

    // Twitter Card tags
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . htmlspecialchars($meta['title']) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . htmlspecialchars($meta['description']) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . htmlspecialchars($meta['image']) . '">' . "\n";
    echo '<meta name="twitter:creator" content="@haerriz">' . "\n";
    echo '<meta name="twitter:site" content="@haerriz">' . "\n";

    // Article specific tags
    if (isset($meta['article'])) {
        echo '<meta property="article:published_time" content="' . htmlspecialchars($meta['article']['published_time']) . '">' . "\n";
        echo '<meta property="article:modified_time" content="' . htmlspecialchars($meta['article']['modified_time']) . '">' . "\n";
        echo '<meta property="article:author" content="' . htmlspecialchars($meta['article']['author']) . '">' . "\n";
        echo '<meta property="article:section" content="' . htmlspecialchars($meta['article']['section']) . '">' . "\n";
    }

    // Additional SEO tags
    echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">' . "\n";
    echo '<meta name="author" content="Haerriz">' . "\n";
    echo '<meta name="theme-color" content="#2196F3">' . "\n";
    echo '<meta name="application-name" content="Haerriz Trip Finance">' . "\n";
}

function getStructuredDataBreadcrumbs($breadcrumbs = []) {
    $items = [];
    $position = 1;

    foreach ($breadcrumbs as $name => $url) {
        $items[] = [
            "@type" => "ListItem",
            "position" => $position,
            "name" => $name,
            "item" => "https://expenses.haerriz.com" . $url
        ];
        $position++;
    }

    return [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => $items
    ];
}
?>