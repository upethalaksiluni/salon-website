document.addEventListener('DOMContentLoaded', function() {
    // Function to mark notification as read
    window.markAsRead = function(notificationId) {
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
                // Update UI without reloading
                const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notification) {
                    notification.classList.remove('unread');
                    const markReadBtn = notification.querySelector('.mark-read-btn');
                    if (markReadBtn) {
                        markReadBtn.remove();
                    }
                }
                
                // Update notification badge count
                updateNotificationCount();
            }
        })
        .catch(error => console.error('Error:', error));
    };

    // Function to update notification badge count
    function updateNotificationCount() {
        fetch('get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                } else {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.textContent = data.count;
                    document.querySelector('.notifications').appendChild(newBadge);
                }
            } else if (badge) {
                badge.remove();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Check for new notifications periodically
    setInterval(updateNotificationCount, 30000);
});