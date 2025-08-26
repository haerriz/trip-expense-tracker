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
            console.log('Initializing push notifications...');
            
            // Request notification permission on load
            const permissionGranted = await this.requestPermission();
            console.log('Permission granted:', permissionGranted);
            
            // Subscribe to push notifications if permission granted
            if (Notification.permission === 'granted') {
                console.log('Subscribing user to push notifications...');
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
            console.log('Getting service worker registration...');
            const registration = await navigator.serviceWorker.ready;
            console.log('Service worker ready:', registration);
            
            // Check if already subscribed
            const existingSubscription = await registration.pushManager.getSubscription();
            if (existingSubscription) {
                console.log('User already subscribed:', existingSubscription);
                await this.sendSubscriptionToServer(existingSubscription);
                return;
            }
            
            console.log('Creating new subscription...');
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
            });
            
            console.log('New subscription created:', subscription);

            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);
            console.log('User subscribed to push notifications');
        } catch (error) {
            console.error('Failed to subscribe user:', error);
        }
    }

    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('api/save_push_subscription.php', {
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
                // Show success message
                if (window.M && window.M.toast) {
                    M.toast({html: 'Push notifications enabled!'});
                }
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
        console.log('sendTestNotification called');
        console.log('Notification permission:', Notification.permission);
        
        if (Notification.permission === 'granted') {
            console.log('Creating test notification');
            
            // Try service worker notification first
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.ready.then(function(registration) {
                    return registration.showNotification('Test Notification', {
                        body: 'This is a test notification from Haerriz Expenses',
                        icon: '/favicon.svg',
                        badge: '/favicon.svg',
                        tag: 'test-notification',
                        requireInteraction: false,
                        vibrate: [200, 100, 200]
                    });
                }).catch(function(error) {
                    console.error('Service worker notification failed:', error);
                    // Fallback to regular notification
                    createRegularNotification();
                });
            } else {
                createRegularNotification();
            }
            
            function createRegularNotification() {
                const notification = new Notification('Test Notification', {
                    body: 'This is a test notification from Haerriz Expenses',
                    icon: '/favicon.svg',
                    badge: '/favicon.svg',
                    tag: 'test-notification',
                    requireInteraction: false
                });
                
                notification.onclick = function() {
                    console.log('Test notification clicked');
                    window.focus();
                    notification.close();
                };
                
                console.log('Regular notification created:', notification);
            }

            
            if (window.M && window.M.toast) {
                M.toast({html: 'Test notification sent!'});
            }
        } else {
            console.log('Notification permission not granted:', Notification.permission);
            if (window.M && window.M.toast) {
                M.toast({html: 'Please enable notifications first'});
            }
        }
    }
    
    // Check subscription status
    async getSubscriptionStatus() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            return { supported: false, subscribed: false };
        }
        
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            return {
                supported: true,
                subscribed: !!subscription,
                permission: Notification.permission
            };
        } catch (error) {
            console.error('Error checking subscription:', error);
            return { supported: true, subscribed: false, error: error.message };
        }
    }
}

// Initialize push notifications
document.addEventListener('DOMContentLoaded', () => {
    window.pushManager = new PushNotificationManager();
});