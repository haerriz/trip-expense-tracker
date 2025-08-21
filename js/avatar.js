function generateAvatar(name) {
    const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F'];
    const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    const color = colors[name.length % colors.length];
    
    const canvas = document.createElement('canvas');
    canvas.width = 40;
    canvas.height = 40;
    const ctx = canvas.getContext('2d');
    
    // Draw circle background
    ctx.fillStyle = color;
    ctx.beginPath();
    ctx.arc(20, 20, 20, 0, 2 * Math.PI);
    ctx.fill();
    
    // Draw initials
    ctx.fillStyle = 'white';
    ctx.font = 'bold 16px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(initials, 20, 20);
    
    return canvas.toDataURL();
}

function updateUserAvatars() {
    document.querySelectorAll('.profile-pic, .member-pic').forEach(img => {
        if (!img.src || img.src.includes('placeholder') || img.src.includes('via.placeholder')) {
            const name = img.alt || img.getAttribute('data-name') || 'User';
            img.src = generateAvatar(name);
        }
    });
}