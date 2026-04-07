<?php
// Performance optimization and Core Web Vitals improvements

class PerformanceOptimizer {

    public static function addPerformanceHeaders() {
        // Security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Performance headers
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Vary: Accept-Encoding');

        // Compression
        if (!headers_sent() && extension_loaded('zlib')) {
            ini_set('zlib.output_compression', 'On');
            ini_set('zlib.output_compression_level', '6');
        }
    }

    public static function optimizeAssets($content) {
        // Minify HTML
        $content = self::minifyHTML($content);

        // Add preload hints for critical resources
        $preloadHints = self::getPreloadHints();
        $content = str_replace('<head>', '<head>' . $preloadHints, $content);

        return $content;
    }

    private static function minifyHTML($html) {
        // Remove comments
        $html = preg_replace('/<!--(.*?)-->/s', '', $html);

        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);

        // Remove multiple spaces
        $html = preg_replace('/\s+/', ' ', $html);

        return $html;
    }

    private static function getPreloadHints() {
        $hints = '';

        // Preload critical CSS
        $hints .= '<link rel="preload" href="css/style.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';

        // Preload critical fonts
        $hints .= '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" as="style">';

        // Preload critical JS
        $hints .= '<link rel="preload" href="https://code.jquery.com/jquery-3.6.0.min.js" as="script">';

        // DNS prefetch for external domains
        $hints .= '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
        $hints .= '<link rel="dns-prefetch" href="//fonts.gstatic.com">';
        $hints .= '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">';
        $hints .= '<link rel="dns-prefetch" href="//code.jquery.com">';

        return $hints;
    }

    public static function generateCriticalCSS() {
        // Extract critical CSS for above-the-fold content
        return '
        /* Critical CSS for fast initial render */
        body { margin: 0; font-family: "Roboto", sans-serif; }
        nav { background-color: #1976d2; }
        .brand-logo { font-size: 1.5rem; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        h1 { font-size: 2.5rem; margin: 1rem 0; }
        .btn { background-color: #2196f3; border-radius: 4px; }
        .card { border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        /* Add more critical styles as needed */
        ';
    }

    public static function addServiceWorkerHeaders() {
        // Service worker for caching and offline functionality
        $swPath = __DIR__ . '/../sw.js';
        if (file_exists($swPath)) {
            header('Service-Worker-Allowed: /');
        }
    }

    public static function optimizeImages($html) {
        // Add lazy loading and WebP support
        $html = preg_replace(
            '/<img([^>]+)src=["\']([^"\']+)["\']([^>]*)>/i',
            '<img$1src="$2" loading="lazy" decoding="async"$3>',
            $html
        );

        return $html;
    }

    public static function addResourceHints($html) {
        // Add resource hints for better performance
        $hints = '
        <!-- Resource Hints -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preconnect" href="https://cdnjs.cloudflare.com">
        <link rel="preconnect" href="https://images.unsplash.com">
        ';

        return str_replace('<head>', '<head>' . $hints, $html);
    }
}

// Core Web Vitals monitoring
class CoreWebVitals {

    public static function injectVitalsScript() {
        return '
        <script>
        // Core Web Vitals tracking
        function sendToAnalytics(metric) {
            // Send to your analytics service
            console.log("Core Web Vital:", metric.name, metric.value);
        }

        // Largest Contentful Paint (LCP)
        new PerformanceObserver((list) => {
            const entries = list.getEntries();
            const lastEntry = entries[entries.length - 1];
            sendToAnalytics({
                name: "LCP",
                value: lastEntry.startTime
            });
        }).observe({entryTypes: ["largest-contentful-paint"]});

        // First Input Delay (FID)
        new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                sendToAnalytics({
                    name: "FID",
                    value: entry.processingStart - entry.startTime
                });
            }
        }).observe({entryTypes: ["first-input"]});

        // Cumulative Layout Shift (CLS)
        let clsValue = 0;
        new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            }
            sendToAnalytics({
                name: "CLS",
                value: clsValue
            });
        }).observe({entryTypes: ["layout-shift"]});
        </script>
        ';
    }

    public static function optimizeForMobile() {
        // Mobile-specific optimizations
        return '
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        ';
    }
}

// CDN configuration for static assets
class CDNManager {

    public static function getCDNUrl($asset) {
        // Configure CDN URLs for static assets
        $cdnBase = 'https://cdn.expenses.haerriz.com/';

        $cdnAssets = [
            'css/style.css' => $cdnBase . 'css/style.min.css',
            'js/dashboard.js' => $cdnBase . 'js/dashboard.min.js',
            'js/auth.js' => $cdnBase . 'js/auth.min.js',
            // Add more assets as needed
        ];

        return $cdnAssets[$asset] ?? $asset;
    }

    public static function enableCDN($html) {
        // Replace local asset URLs with CDN URLs
        $html = str_replace('css/style.css', self::getCDNUrl('css/style.css'), $html);
        $html = str_replace('js/dashboard.js', self::getCDNUrl('js/dashboard.js'), $html);
        $html = str_replace('js/auth.js', self::getCDNUrl('js/auth.js'), $html);

        return $html;
    }
}

// Usage in your PHP files:
/*
// At the top of your PHP files
require_once 'config/performance.php';
PerformanceOptimizer::addPerformanceHeaders();
CoreWebVitals::injectVitalsScript();
*/
?>