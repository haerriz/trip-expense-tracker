// Clear Service Worker cache and force logout
function clearCacheAndLogout() {
    if ('serviceWorker' in navigator) {
        // Clear all caches
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    return caches.delete(cacheName);
                })
            );
        }).then(function() {
            // Force reload to clear any cached session data
            window.location.href = '/logout.php';
        });
    } else {
        window.location.href = '/logout.php';
    }
}

// Add to logout links
document.addEventListener('DOMContentLoaded', function() {
    const logoutLinks = document.querySelectorAll('a[href="logout.php"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            clearCacheAndLogout();
        });
    });
});