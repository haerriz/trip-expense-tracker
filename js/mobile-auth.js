// Mobile-specific Google OAuth handling
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Override Google OAuth configuration for mobile
if (isMobile()) {
    // Use redirect mode for mobile
    window.addEventListener('load', function() {
        const gOnload = document.getElementById('g_id_onload');
        if (gOnload) {
            gOnload.setAttribute('data-ux_mode', 'redirect');
            gOnload.setAttribute('data-login_uri', 'https://expenses.haerriz.com/google-auth-callback.php');
            gOnload.removeAttribute('data-callback');
        }
    });
} else {
    // Keep popup mode for desktop
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
                    window.location.href = 'dashboard.php';
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
}