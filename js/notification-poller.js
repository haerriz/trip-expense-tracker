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
        }, 3000); // Check every 3 seconds for faster response
        
    }
    
    async checkForNotifications() {
        try {
            const response = await fetch('api/check_notifications.php');
            const data = await response.json();
            
            
            if (data.success && data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    // Only show notifications we haven't seen before
                    const notificationTime = new Date(notification.created_at).getTime();
                    if (notificationTime > this.lastCheck) {
                        this.showNotification(notification);
                    }
                });
                // Update last check time
                this.lastCheck = Date.now();
            }
        } catch (error) {
        }
    }
    
    showNotification(notification) {
        
        if (Notification.permission === 'granted') {
            // Use the same method as test notifications (which work!)
            const realNotification = new Notification('🔔 ' + notification.title, {
                body: notification.message,
                icon: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8Y2lyY2xlIGN4PSIzMiIgY3k9IjMyIiByPSIzMCIgZmlsbD0iI0ZGRkZGRiIgc3Ryb2tlPSJub25lIi8+CiAgPGNpcmNsZSBjeD0iMzIiIGN5PSIzMiIgcj0iMjYiIGZpbGw9InRyYW5zcGFyZW50IiBzdHJva2U9IiMwMDAwMDAiIHN0cm9rZS13aWR0aD0iMiIvPgogIDx0ZXh0IHg9IjMyIiB5PSIzOCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjgiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSIjMDAwMDAwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5IQUVSUklaPC90ZXh0Pgo8L3N2Zz4=',
                badge: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8Y2lyY2xlIGN4PSIzMiIgY3k9IjMyIiByPSIzMCIgZmlsbD0iI0ZGRkZGRiIgc3Ryb2tlPSJub25lIi8+CiAgPGNpcmNsZSBjeD0iMzIiIGN5PSIzMiIgcj0iMjYiIGZpbGw9InRyYW5zcGFyZW50IiBzdHJva2U9IiMwMDAwMDAiIHN0cm9rZS13aWR0aD0iMiIvPgogIDx0ZXh0IHg9IjMyIiB5PSIzOCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjgiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSIjMDAwMDAwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5IQUVSUklaPC90ZXh0Pgo8L3N2Zz4=',
                vibrate: [300, 100, 300],
                requireInteraction: true,
                tag: 'real-push-' + notification.id,
                timestamp: Date.now()
            });
            
            realNotification.onclick = function() {
                window.focus();
                realNotification.close();
            };
            
        } else {
        }
    }
    
    stop() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }
    }
}

// Initialize notification poller when page loads
document.addEventListener('DOMContentLoaded', () => {
    if (Notification.permission === 'granted') {
        window.notificationPoller = new NotificationPoller();
    }
});