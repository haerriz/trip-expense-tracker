// Admin Push Notifications Handler
$(document).ready(function() {
    console.log('Admin notifications loaded');
    
    // Test API connectivity
    $.get('api/test_notification.php')
        .done(function(data) {
            console.log('API test successful:', data);
        })
        .fail(function(xhr, status, error) {
            console.error('API test failed:', {xhr, status, error});
        });
    
    loadNotificationStats();
    loadNotificationHistory();
    
    // Send notification form
    $('#notification-form').on('submit', function(e) {
        e.preventDefault();
        sendPushNotification();
    });
    
    // Test notification button
    $('#test-notification').on('click', function() {
        testNotification();
    });
    
    // Manual subscription button
    $('#subscribe-push').on('click', function() {
        if (window.pushManager) {
            window.pushManager.subscribeUser();
        } else {
            M.toast({html: 'Push manager not available'});
        }
    });
    
    // Check subscription status
    $('#check-subscription').on('click', function() {
        checkSubscriptionStatus();
    });
    
    // Debug subscriptions
    $('#debug-subscriptions').on('click', function() {
        debugSubscriptions();
    });
});

function sendPushNotification() {
    const title = $('#notification-title').val();
    const message = $('#notification-message').val();
    
    if (!title || !message) {
        M.toast({html: 'Please fill in both title and message'});
        return;
    }
    
    console.log('Sending notification:', {title, message});
    
    $.ajax({
        url: 'api/send_push_notification.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            title: title,
            message: message
        })
    })
    .done(function(data) {
        console.log('Response:', data);
        if (data.success) {
            M.toast({html: data.message});
            $('#notification-form')[0].reset();
            M.updateTextFields();
            loadNotificationStats();
            loadNotificationHistory();
        } else {
            M.toast({html: data.error || 'Failed to send notification'});
        }
    })
    .fail(function(xhr, status, error) {
        console.error('AJAX Error:', {xhr, status, error});
        console.error('Response text:', xhr.responseText);
        M.toast({html: 'Network error: ' + error});
    });
}

function testNotification() {
    console.log('Test notification clicked');
    console.log('Push manager available:', !!window.pushManager);
    
    if (window.pushManager) {
        console.log('Calling sendTestNotification');
        window.pushManager.sendTestNotification();
    } else {
        console.log('Push manager not available');
        M.toast({html: 'Push manager not available'});
        
        // Fallback: try direct browser notification
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                new Notification('Test Notification', {
                    body: 'This is a test notification from admin panel',
                    icon: '/favicon.svg'
                });
            } else {
                M.toast({html: 'Notification permission not granted'});
            }
        } else {
            M.toast({html: 'Notifications not supported'});
        }
    }
}

function loadNotificationStats() {
    $.get('api/get_notification_stats.php')
        .done(function(data) {
            if (data.success) {
                $('#subscriber-count').text(data.subscribers || 0);
                $('#notifications-sent').text(data.sent_today || 0);
            }
        })
        .fail(function() {
            console.error('Failed to load notification stats');
        });
}

function loadNotificationHistory() {
    $.get('api/get_notification_history.php')
        .done(function(data) {
            if (data.success) {
                let html = '';
                if (data.history && data.history.length > 0) {
                    data.history.forEach(function(notification) {
                        const date = new Date(notification.sent_at).toLocaleDateString();
                        html += `
                            <div class="notification-history-item">
                                <strong>${notification.title}</strong><br>
                                <small>${notification.message}</small><br>
                                <span class="grey-text">${date} - ${notification.recipients_count} recipients</span>
                            </div>
                        `;
                    });
                } else {
                    html = '<p class="grey-text">No notifications sent yet</p>';
                }
                $('#notification-history').html(html);
            }
        })
        .fail(function() {
            console.error('Failed to load notification history');
        });
}

function checkSubscriptionStatus() {
    console.log('Checking subscription status...');
    
    if (window.pushManager) {
        window.pushManager.getSubscriptionStatus().then(function(status) {
            console.log('Subscription status:', status);
            
            let message = `
                Supported: ${status.supported}<br>
                Subscribed: ${status.subscribed}<br>
                Permission: ${status.permission}
            `;
            
            if (status.error) {
                message += `<br>Error: ${status.error}`;
            }
            
            M.toast({html: message, displayLength: 6000});
        });
    } else {
        M.toast({html: 'Push manager not available'});
    }
}

function debugSubscriptions() {
    console.log('Debugging subscriptions...');
    
    $.get('api/debug_subscriptions.php')
        .done(function(data) {
            console.log('Debug data:', data);
            
            if (data.success) {
                let message = `
                    Total DB entries: ${data.counts.total}<br>
                    Valid subscriptions: ${data.counts.valid}<br>
                `;
                
                if (data.valid_subscriptions.length > 0) {
                    message += `<br>Sample endpoint: ${data.valid_subscriptions[0].endpoint.substring(0, 50)}...`;
                }
                
                M.toast({html: message, displayLength: 8000});
            } else {
                M.toast({html: 'Debug failed: ' + data.error});
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Debug failed:', {xhr, status, error});
            M.toast({html: 'Debug request failed'});
        });
}