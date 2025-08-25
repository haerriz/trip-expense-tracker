// Simple universal Google OAuth handler
window.handleCredentialResponse = function(response) {
    
    fetch('/google-auth.php', {
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
            alert('Authentication error occurred');
        }
    })
    .catch(error => {
        alert('Network error during authentication');
    });
};