// Mobile OAuth with in-page iframe solution
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Desktop callback (unchanged)
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

// Mobile-specific implementation
if (isMobile()) {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Mobile device detected - switching to iframe OAuth');
        
        // Hide desktop OAuth, show mobile OAuth
        document.getElementById('desktop-oauth').style.display = 'none';
        document.getElementById('mobile-oauth').style.display = 'block';
        
        // Initialize modal
        const modal = M.Modal.init(document.getElementById('mobile-oauth-modal'), {
            dismissible: true,
            onCloseEnd: function() {
                document.getElementById('google-auth-iframe').src = '';
            }
        });
        
        // Mobile Google button click
        document.getElementById('mobile-google-btn').addEventListener('click', function() {
            const clientId = '435239215784-eckha7a4i5fg8ik7u7f7h750nc2upibh.apps.googleusercontent.com';
            const redirectUri = 'https://expenses.haerriz.com/mobile-oauth-handler.php';
            const scope = 'email profile';
            
            const authUrl = `https://accounts.google.com/oauth/v2/auth?` +
                `client_id=${clientId}&` +
                `redirect_uri=${encodeURIComponent(redirectUri)}&` +
                `response_type=code&` +
                `scope=${encodeURIComponent(scope)}&` +
                `access_type=offline`;
            
            // Load auth URL in iframe
            document.getElementById('google-auth-iframe').src = authUrl;
            modal.open();
        });
        
        // Listen for messages from iframe
        window.addEventListener('message', function(event) {
            if (event.origin !== 'https://expenses.haerriz.com') return;
            
            if (event.data.type === 'GOOGLE_AUTH_SUCCESS') {
                modal.close();
                window.location.href = 'dashboard.php';
            } else if (event.data.type === 'GOOGLE_AUTH_ERROR') {
                modal.close();
                alert('Google authentication failed: ' + event.data.message);
            }
        });
    });
}