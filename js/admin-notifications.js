// Admin Push Notifications Handler
$(document).ready(function() {
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
});

function sendPushNotification() {
    const title = $('#notification-title').val();
    const message = $('#notification-message').val();
    
    if (!title || !message) {
        M.toast({html: 'Please fill in both title and message'});
        return;
    }
    
    $.post('api/send_push_notification.php', {
        title: title,
        message: message
    })
    .done(function(data) {
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
    .fail(function() {
        M.toast({html: 'Network error sending notification'});
    });
}

function testNotification() {
    if (window.pushManager) {
        window.pushManager.sendTestNotification();
    } else {
        M.toast({html: 'Push manager not available'});
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