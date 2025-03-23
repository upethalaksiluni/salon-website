function checkNotifications() {
    fetch('check_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? 'block' : 'none';
                }
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}

// Check notifications every 30 seconds
setInterval(checkNotifications, 30000);

// Initial check
document.addEventListener('DOMContentLoaded', checkNotifications);

// Mark notification as read
document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', function() {
        const notificationId = this.dataset.id;
        markAsRead(notificationId);
    });
});

function markAsRead(notificationId) {
    if (!notificationId) return;

    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationUI(notificationId);
            updateUnreadCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateNotificationUI(notificationId) {
    const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
    if (notification) {
        notification.classList.remove('unread');
        const actionBtn = notification.querySelector('.notification-actions');
        if (actionBtn) {
            actionBtn.remove();
        }
        
        // Add fade effect
        notification.style.opacity = '0.7';
        setTimeout(() => {
            notification.style.opacity = '1';
        }, 300);
    }
}

function updateUnreadCount() {
    const badge = document.querySelector('.badge.bg-primary');
    if (badge) {
        const currentCount = parseInt(badge.textContent);
        if (currentCount > 1) {
            badge.textContent = `${currentCount - 1} unread`;
        } else {
            badge.remove();
        }
    }
}

// Add animations when notifications load
document.addEventListener('DOMContentLoaded', function() {
    const notifications = document.querySelectorAll('.notification-item');
    notifications.forEach((notification, index) => {
        setTimeout(() => {
            notification.classList.add('show');
        }, index * 100);
    });
});

// Check for new notifications periodically
function checkNewNotifications() {
    fetch('check_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.hasNew) {
                // Handle new notifications
                updateNotificationsList(data.notifications);
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}

// Start periodic checks
setInterval(checkNewNotifications, 30000); // Check every 30 seconds