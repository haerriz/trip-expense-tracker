// Simple mobile OAuth fix
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Global credential response handler
window.handleCredentialResponse = function(response) {
    console.log('Google OAuth response received');
    
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
                // For mobile, use location.replace to prevent back button issues
                if (isMobile()) {
                    window.location.replace('dashboard.php');
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

// Mobile-specific enhancements
if (isMobile()) {
    document.addEventListener('DOMContentLoaded', function() {
        // Add mobile CSS for better button display
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
    });
}