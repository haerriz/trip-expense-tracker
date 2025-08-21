// Mobile Google OAuth fix
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Global credential response handler with mobile fix
window.handleCredentialResponse = function(response) {
    console.log('Google OAuth response received');
    
    fetch('google-auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ credential: response.credential })
    })
    .then(response => response.text())
    .then(text => {
        console.log('Auth response:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                // Mobile-specific redirect handling
                if (isMobile()) {
                    // Force immediate redirect on mobile
                    window.location.href = 'dashboard.php';
                    // Backup redirect after short delay
                    setTimeout(() => {
                        window.location.replace('dashboard.php');
                    }, 100);
                } else {
                    window.location.href = 'dashboard.php';
                }
            } else {
                alert('Google authentication failed: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error('JSON parse error:', e, 'Response:', text);
            alert('Authentication error occurred');
        }
    })
    .catch(error => {
        console.error('Google auth error:', error);
        alert('Network error during authentication');
    });
};

// Mobile-specific initialization
if (isMobile()) {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Mobile device detected, applying mobile OAuth fixes');
        
        // Override Google Sign-In button behavior for mobile
        setTimeout(() => {
            const googleButton = document.querySelector('.g_id_signin');
            if (googleButton) {
                googleButton.addEventListener('click', function(e) {
                    console.log('Google button clicked on mobile');
                    // Ensure popup isn't blocked
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Trigger Google sign-in manually
                    if (window.google && window.google.accounts) {
                        window.google.accounts.id.prompt();
                    }
                });
            }
        }, 2000);
        
        // Add mobile-specific CSS
        const style = document.createElement('style');
        style.textContent = `
            @media (max-width: 768px) {
                .g_id_signin {
                    width: 100% !important;
                    min-height: 44px !important;
                }
                .g_id_signin > div {
                    width: 100% !important;
                }
            }
        `;
        document.head.appendChild(style);
    });
}