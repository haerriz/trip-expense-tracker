<?php
// Rich snippets and structured data for better search visibility

function getSoftwareApplicationSchema() {
    return [
        "@context" => "https://schema.org/",
        "@type" => "SoftwareApplication",
        "name" => "Haerriz Trip Finance",
        "description" => "AI-powered expense tracker for backpackers and group travel with intelligent suggestions, multi-currency support, and real-time budget analytics",
        "url" => "https://expenses.haerriz.com",
        "image" => "https://expenses.haerriz.com/assets/logo.png",
        "applicationCategory" => "FinanceApplication",
        "operatingSystem" => "Web Browser",
        "offers" => [
            "@type" => "Offer",
            "price" => "0",
            "priceCurrency" => "USD",
            "availability" => "https://schema.org/InStock"
        ],
        "aggregateRating" => [
            "@type" => "AggregateRating",
            "ratingValue" => "4.8",
            "ratingCount" => "150",
            "bestRating" => "5",
            "worstRating" => "1"
        ],
        "author" => [
            "@type" => "Person",
            "name" => "Haerriz",
            "url" => "https://expenses.haerriz.com"
        ],
        "featureList" => [
            "AI Expense Suggestions",
            "Multi-Currency Support",
            "Group Expense Splitting",
            "Real-time Budget Tracking",
            "Receipt Analysis",
            "Travel Analytics"
        ]
    ];
}

function getFAQSchema() {
    return [
        "@context" => "https://schema.org",
        "@type" => "FAQPage",
        "mainEntity" => [
            [
                "@type" => "Question",
                "name" => "How does AI help with expense tracking?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Our AI analyzes your spending patterns and suggests upcoming expenses, analyzes receipts automatically, and provides personalized budget advice to help you stay on track during your travels."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "How do I split expenses with my travel group?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Create a trip, invite members by email, and add expenses. Our system automatically splits costs equally or allows custom splits. Members can view their individual shares in real-time."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Does Haerriz support multiple currencies?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Yes, we support 9 major currencies (USD, EUR, GBP, JPY, AUD, CAD, INR, THB, VND) with automatic conversion rates. Set your trip currency and track expenses in multiple currencies seamlessly."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Is Haerriz free to use?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Yes, Haerriz is completely free for all users. No hidden fees, no premium features - everything is included at no cost."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "How secure is my travel expense data?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "We use industry-standard encryption, secure authentication, and never share your personal data. Your trip expenses and financial information are kept private and secure."
                ]
            ]
        ]
    ];
}

function getHowToSchema() {
    return [
        "@context" => "https://schema.org/",
        "@type" => "HowTo",
        "name" => "How to Track Group Travel Expenses with AI",
        "description" => "Complete guide to managing group travel finances using AI-powered expense tracking",
        "totalTime" => "PT10M",
        "step" => [
            [
                "@type" => "HowToStep",
                "position" => 1,
                "name" => "Create Your Trip",
                "text" => "Sign up and create a new trip. Set your trip name, dates, budget, and preferred currency from our 9 supported currencies.",
                "image" => "https://expenses.haerriz.com/assets/step1-create-trip.jpg"
            ],
            [
                "@type" => "HowToStep",
                "position" => 2,
                "name" => "Invite Travel Companions",
                "text" => "Add friends and travel companions to your trip by email. They'll receive invitations and can join instantly.",
                "image" => "https://expenses.haerriz.com/assets/step2-invite-members.jpg"
            ],
            [
                "@type" => "HowToStep",
                "position" => 3,
                "name" => "Add Expenses with AI Help",
                "text" => "Record expenses manually or upload receipts for automatic analysis. Our AI suggests upcoming expenses based on your spending patterns.",
                "image" => "https://expenses.haerriz.com/assets/step3-add-expenses.jpg"
            ],
            [
                "@type" => "HowToStep",
                "position" => 4,
                "name" => "Monitor Budget and Analytics",
                "text" => "View real-time budget tracking, expense breakdowns, and individual shares. Get AI-powered budget advice to stay on track.",
                "image" => "https://expenses.haerriz.com/assets/step4-view-analytics.jpg"
            ]
        ],
        "supply" => [
            [
                "@type" => "HowToSupply",
                "name" => "Smartphone or Computer"
            ],
            [
                "@type" => "HowToSupply",
                "name" => "Internet Connection"
            ]
        ],
        "tool" => [
            [
                "@type" => "HowToTool",
                "name" => "Web Browser"
            ]
        ]
    ];
}

function getOrganizationSchema() {
    return [
        "@context" => "https://schema.org",
        "@type" => "Organization",
        "name" => "Haerriz",
        "url" => "https://expenses.haerriz.com",
        "logo" => "https://expenses.haerriz.com/assets/logo.png",
        "description" => "AI-powered travel expense tracking for backpackers and group travelers",
        "founder" => [
            "@type" => "Person",
            "name" => "Haerriz"
        ],
        "sameAs" => [
            "https://github.com/haerriz"
        ],
        "contactPoint" => [
            "@type" => "ContactPoint",
            "email" => "haerriz@gmail.com",
            "contactType" => "customer service"
        ]
    ];
}

function getWebSiteSchema() {
    return [
        "@context" => "https://schema.org/",
        "@type" => "WebSite",
        "name" => "Haerriz Trip Finance",
        "url" => "https://expenses.haerriz.com",
        "description" => "Free AI-powered expense tracker for backpackers and group travel",
        "inLanguage" => "en-US",
        "copyrightHolder" => [
            "@type" => "Person",
            "name" => "Haerriz"
        ],
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => "https://expenses.haerriz.com/search?q={search_term_string}",
            "query-input" => "required name=search_term_string"
        ]
    ];
}

function getBreadcrumbSchema($page, $breadcrumbs = []) {
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

// Output schema markup as JSON-LD
function outputSchemaMarkup($schemas = []) {
    if (empty($schemas)) {
        // Default schemas for homepage
        $schemas = [
            'software' => getSoftwareApplicationSchema(),
            'faq' => getFAQSchema(),
            'howto' => getHowToSchema(),
            'organization' => getOrganizationSchema(),
            'website' => getWebSiteSchema()
        ];
    }

    echo '<script type="application/ld+json">' . json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}
?>