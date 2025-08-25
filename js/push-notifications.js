// Push Notifications Manager
class PushNotificationManager {
    constructor() {
        this.vapidPublicKey = 'BEl62iUYgUivxIkv69yViEuiBIa40HcCWLEaWXFK3qUTuq2ByjdMYstqf5QjvQoaq30cINhw6lzd4kxoZWGZVBs'; // Demo key
        this.init();
    }

    async init() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.log('Push messaging is not supported');
            return;
        }

        try {
            // Request notification permission on load
            await this.requestPermission();
            
            // Subscribe to push notifications if permission granted
            if (Notification.permission === 'granted') {
                await this.subscribeUser();
            }
        } catch (error) {
            console.error('Push notification init failed:', error);
        }
    }

    async requestPermission() {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            console.log('Notification permission granted');
            return true;
        } else {
            console.log('Notification permission denied');
            return false;
        }
    }

    async subscribeUser() {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
            });

            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);
            console.log('User subscribed to push notifications');
        } catch (error) {
            console.error('Failed to subscribe user:', error);
        }
    }

    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/save_push_subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    subscription: subscription,
                    user_id: window.currentUserId || null
                })
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server error:', errorText);
                return;
            }
            
            const result = await response.json();
            if (result.success) {
                console.log('Subscription saved successfully');
            }
        } catch (error) {
            console.error('Error saving subscription:', error);
        }
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Test notification (for admin)
    async sendTestNotification() {
        if (Notification.permission === 'granted') {
            new Notification('Test Notification', {
                body: 'This is a test notification from Haerriz Expenses',
                icon: '/favicon.svg'
            });
        }
    }
}

// Initialize push notifications
document.addEventListener('DOMContentLoaded', () => {
    window.pushManager = new PushNotificationManager();
});