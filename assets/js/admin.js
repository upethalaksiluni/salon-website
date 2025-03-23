document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.admin-sidebar');
    const mainContent = document.querySelector('.main-content');
    const footer = document.querySelector('.admin-footer');
    const sidebarToggle = document.getElementById('sidebarToggle');

    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
        footer.classList.toggle('collapsed');
        
        // Store sidebar state
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }

    // Initialize sidebar state from localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
        footer.classList.add('collapsed');
    }

    // Add click event to sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    // Handle responsive behavior
    function handleResize() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
            footer.classList.add('collapsed');
        } else {
            if (localStorage.getItem('sidebarCollapsed') !== 'true') {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('collapsed');
                footer.classList.remove('collapsed');
            }
        }
    }

    window.addEventListener('resize', handleResize);
    handleResize(); // Initial check
});

function initializeNotifications() {
    const checkNotifications = () => {
        fetch('api/check_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(data.count);
                }
            })
            .catch(error => console.error('Error checking notifications:', error));
    };

    function updateNotificationBadge(count) {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        }
    }

    // Check notifications every 30 seconds
    setInterval(checkNotifications, 30000);
    checkNotifications(); // Initial check
}

// Initialize Bootstrap tooltips and popovers
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('[data-bs-toggle="popover"]').popover();
});