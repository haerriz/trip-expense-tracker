function handleCredentialResponse(response) {
    console.log('Google OAuth response received');
    
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
                window.location.href = '/dashboard';
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
}

function decodeJwtResponse(token) {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
    
    return JSON.parse(jsonPayload);
}