function showSignup() {
    document.getElementById('login-form').style.display = 'none';
    document.getElementById('signup-form').style.display = 'block';
}

function showLogin() {
    document.getElementById('signup-form').style.display = 'none';
    document.getElementById('login-form').style.display = 'block';
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    fetch('/manual-login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                window.location.href = '/dashboard.php';
            } else {
                alert(data.message || 'Login failed');
            }
        } catch (e) {
            console.error('JSON parse error:', e, 'Response:', text);
            alert('Server error occurred. Please try again.');
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        alert('Network error. Please check your connection.');
    });
});

document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Skip reCAPTCHA for now
    const name = document.getElementById('signupName').value;
    const email = document.getElementById('signupEmail').value;
    const phone = document.getElementById('signupPhone').value;
    const password = document.getElementById('signupPassword').value;
    
    fetch('/manual-signup.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, phone, password })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert('Account created successfully! Please login.');
                showLogin();
            } else {
                alert(data.message || 'Signup failed');
            }
        } catch (e) {
            console.error('JSON parse error:', e, 'Response:', text);
            alert('Server error occurred. Please try again.');
        }
    })
    .catch(error => {
        console.error('Signup error:', error);
        alert('Network error. Please check your connection.');
    });
});