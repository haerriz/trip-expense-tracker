// Mobile detection and OAuth handling
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Initialize mobile/desktop specific OAuth
document.addEventListener('DOMContentLoaded', function() {
    if (isMobile()) {
        console.log('Mobile device detected');
        
        // Hide desktop OAuth, show mobile OAuth
        document.getElementById('g_id_onload_desktop').style.display = 'none';
        document.getElementById('g_id_onload_mobile').style.display = 'block';
        
        // Add mobile CSS
        const style = document.createElement('style');
        style.textContent = `
            .mobile-only { display: block !important; }
            .desktop-only { display: none !important; }
            .g_id_signin { width: 100% !important; }
        `;
        document.head.appendChild(style);
        
    } else {
        console.log('Desktop device detected');
        
        // Show desktop OAuth, hide mobile OAuth
        document.getElementById('g_id_onload_desktop').style.display = 'block';
        document.getElementById('g_id_onload_mobile').style.display = 'none';
    }
});

// Desktop popup callback
window.handleCredentialResponse = function(response) {
    console.log('Desktop Google OAuth response received');
    
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