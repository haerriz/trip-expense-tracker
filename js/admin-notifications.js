// Admin Push Notification Management
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize notification form
    const notificationForm = document.getElementById('notification-form');
    const testNotificationBtn = document.getElementById('test-notification');
    
    if (notificationForm) {
        notificationForm.addEventListener('submit', sendPushNotification);
    }
    
    if (testNotificationBtn) {
        testNotificationBtn.addEventListener('click', sendTestNotification);
    }
    
    // Load notification stats
    loadNotificationStats();
    loadNotificationHistory();
});

async function sendPushNotification(e) {
    e.preventDefault();
    
    const title = document.getElementById('notification-title').value;
    const message = document.getElementById('notification-message').value;
    
    if (!title || !message) {
        M.toast({html: 'Please fill in all fields', classes: 'red'});
        return;
    }
    
    try {
        const response = await fetch('/api/send_push_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                title: title,
                message: message
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            M.toast({html: result.message, classes: 'green'});
            document.getElementById('notification-form').reset();
            loadNotificationStats();
            loadNotificationHistory();
        } else {
            M.toast({html: result.error || 'Failed to send notification', classes: 'red'});
        }
        
    } catch (error) {
        console.error('Error sending notification:', error);
        M.toast({html: 'Error sending notification', classes: 'red'});
    }
}

function sendTestNotification() {
    if (window.pushManager) {
        window.pushManager.sendTestNotification();
        M.toast({html: 'Test notification sent to your device', classes: 'blue'});
    } else {
        M.toast({html: 'Push notifications not available', classes: 'orange'});
    }
}

async function loadNotificationStats() {
    try {
        const response = await fetch('/api/get_notification_stats.php');
        
        if (!response.ok) {
            console.error('Stats API error:', response.status);
            return;
        }
        
        const stats = await response.json();
        
        if (stats.success) {
            document.getElementById('subscriber-count').textContent = stats.subscribers || 0;
            document.getElementById('notifications-sent').textContent = stats.sent_today || 0;
        }
    } catch (error) {
        console.error('Error loading notification stats:', error);
        // Set default values on error
        document.getElementById('subscriber-count').textContent = '0';
        document.getElementById('notifications-sent').textContent = '0';
    }
}

async function loadNotificationHistory() {
    try {
        const response = await fetch('/api/get_notification_history.php');
        
        if (!response.ok) {
            console.error('History API error:', response.status);
            return;
        }
        
        const history = await response.json();
        const historyContainer = document.getElementById('notification-history');
        
        if (history.success && history.notifications.length > 0) {
            historyContainer.innerHTML = history.notifications.map(notification => `
                <div class="notification-item" style="padding: 8px 0; border-bottom: 1px solid #eee;">
                    <div style="font-weight: 500; font-size: 0.9rem;">${notification.title}</div>
                    <div style="font-size: 0.8rem; color: #666;">${notification.message}</div>
                    <div style="font-size: 0.7rem; color: #999; margin-top: 4px;">
                        ${notification.recipients_count} recipients • ${new Date(notification.sent_at).toLocaleDateString()}
                    </div>
                </div>
            `).join('');
        } else {
            historyContainer.innerHTML = '<p style="color: #666; font-size: 0.9rem;">No notifications sent yet</p>';
        }
    } catch (error) {
        console.error('Error loading notification history:', error);
        const historyContainer = document.getElementById('notification-history');
        historyContainer.innerHTML = '<p style="color: #666; font-size: 0.9rem;">Error loading history</p>';
    }
}