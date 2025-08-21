// Enhanced mobile Google OAuth handling
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Global credential response handler
window.handleCredentialResponse = function(response) {
    console.log('Google OAuth response received');
    
    // Show loading indicator
    if (isMobile()) {
        document.body.style.opacity = '0.7';
        document.body.style.pointerEvents = 'none';
    }
    
    fetch('google-auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ credential: response.credential })
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                // Force page reload on mobile to ensure proper redirect
                if (isMobile()) {
                    window.location.replace('dashboard.php');
                } else {
                    window.location.href = 'dashboard.php';
                }
            } else {
                if (isMobile()) {
                    document.body.style.opacity = '1';
                    document.body.style.pointerEvents = 'auto';
                }
                alert('Google authentication failed: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error('JSON parse error:', e, 'Response:', text);
            if (isMobile()) {
                document.body.style.opacity = '1';
                document.body.style.pointerEvents = 'auto';
            }
            alert('Authentication error occurred');
        }
    })
    .catch(error => {
        console.error('Google auth error:', error);
        if (isMobile()) {
            document.body.style.opacity = '1';
            document.body.style.pointerEvents = 'auto';
        }
        alert('Network error during authentication');
    });
};

// Mobile-specific enhancements
if (isMobile()) {
    document.addEventListener('DOMContentLoaded', function() {
        // Add mobile-specific styling for Google button
        const style = document.createElement('style');
        style.textContent = `
            .g_id_signin {
                width: 100% !important;
            }
            .g_id_signin > div {
                width: 100% !important;
            }
        `;
        document.head.appendChild(style);
        
        // Prevent popup blocking by ensuring user gesture
        document.addEventListener('click', function(e) {
            if (e.target.closest('.g_id_signin')) {
                // This ensures the popup is opened in response to user gesture
                setTimeout(() => {
                    console.log('Google sign-in clicked on mobile');
                }, 0);
            }
        });
    });
}