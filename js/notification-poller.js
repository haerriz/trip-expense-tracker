// Simple notification polling system (alternative to FCM)
class NotificationPoller {
    constructor() {
        this.lastCheck = Date.now();
        this.pollInterval = null;
        this.init();
    }
    
    init() {
        // Start polling every 10 seconds
        this.pollInterval = setInterval(() => {
            this.checkForNotifications();
        }, 10000);
        
        console.log('Notification poller started');
    }
    
    async checkForNotifications() {
        try {
            const response = await fetch('api/check_notifications.php');
            const data = await response.json();
            
            if (data.success && data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    this.showNotification(notification);
                });
            }
        } catch (error) {
            console.error('Notification polling error:', error);
        }
    }
    
    showNotification(notification) {
        console.log('Showing polled notification:', notification);
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.ready.then(registration => {
                return registration.showNotification(notification.title, {
                    body: notification.message,
                    icon: '/favicon.svg',
                    badge: '/favicon.svg',
                    tag: 'polled-notification-' + notification.id,
                    vibrate: [200, 100, 200]
                });
            });
        } else if (Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: '/favicon.svg'
            });
        }
    }
    
    stop() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            console.log('Notification poller stopped');
        }
    }
}

// Initialize notification poller when page loads
document.addEventListener('DOMContentLoaded', () => {
    if (Notification.permission === 'granted') {
        window.notificationPoller = new NotificationPoller();
    }
});