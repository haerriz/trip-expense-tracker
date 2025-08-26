// Push Notifications Manager
class PushNotificationManager {
    constructor() {
        this.vapidPublicKey = 'BEl62iUYgUivxIkv69yViEuiBIa40HcCWLEaWXFK3qUTuq2ByjdMYstqf5QjvQoaq30cINhw6lzd4kxoZWGZVBs'; // Demo key
        this.init();
    }

    async init() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            return;
        }

        try {
            
            // Request notification permission on load
            const permissionGranted = await this.requestPermission();
            
            // Subscribe to push notifications if permission granted
            if (Notification.permission === 'granted') {
                await this.subscribeUser();
            }
        } catch (error) {
        }
    }

    async requestPermission() {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            return true;
        } else {
            return false;
        }
    }

    async subscribeUser() {
        try {
            const registration = await navigator.serviceWorker.ready;
            
            // Check if already subscribed
            const existingSubscription = await registration.pushManager.getSubscription();
            if (existingSubscription) {
                await this.sendSubscriptionToServer(existingSubscription);
                return;
            }
            
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
            });
            

            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);
        } catch (error) {
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
                return;
            }
            
            const result = await response.json();
            if (result.success) {
                // Show success message
                if (window.M && window.M.toast) {
                    M.toast({html: 'Push notifications enabled!'});
                }
            }
        } catch (error) {
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
                    window.focus();
                    notification.close();
                };
                
            }

            
            if (window.M && window.M.toast) {
                M.toast({html: 'Test notification sent!'});
            }
        } else {
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
            return { supported: true, subscribed: false, error: error.message };
        }
    }
}

// Initialize push notifications
document.addEventListener('DOMContentLoaded', () => {
    window.pushManager = new PushNotificationManager();
});