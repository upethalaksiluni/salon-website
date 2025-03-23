document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const sidebar = document.getElementById('clientSidebar');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const mainContent = document.querySelector('.main-content');
    const footer = document.querySelector('.client-footer');

    // Check if elements exist
    if (!sidebar || !toggleBtn) {
        console.error('Required sidebar elements not found');
        return;
    }

    // Toggle function
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        if (mainContent) mainContent.classList.toggle('expanded');
        if (footer) footer.classList.toggle('expanded');

        // Update toggle icon
        const icon = toggleBtn.querySelector('i');
        if (icon) {
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-bars-staggered');
            } else {
                icon.classList.remove('fa-bars-staggered');
                icon.classList.add('fa-bars');
            }
        }

        // Save state
        localStorage.setItem('sidebarState', 
            sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded'
        );

        // Update tooltips
        updateTooltips();
    }

    // Toggle event listener
    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        toggleSidebar();
    });

    // Initialize tooltips
    function updateTooltips() {
        const menuItems = document.querySelectorAll('.sidebar-menu a');
        menuItems.forEach(item => {
            try {
                if (sidebar.classList.contains('collapsed')) {
                    const text = item.querySelector('span')?.textContent || '';
                    if (text) {
                        item.setAttribute('data-bs-toggle', 'tooltip');
                        item.setAttribute('data-bs-placement', 'right');
                        item.setAttribute('data-bs-title', text);
                        new bootstrap.Tooltip(item);
                    }
                } else {
                    const tooltip = bootstrap.Tooltip.getInstance(item);
                    if (tooltip) {
                        tooltip.dispose();
                    }
                    item.removeAttribute('data-bs-toggle');
                    item.removeAttribute('data-bs-placement');
                    item.removeAttribute('data-bs-title');
                }
            } catch (error) {
                console.error('Error updating tooltip:', error);
            }
        });
    }

    // Load saved state
    const savedState = localStorage.getItem('sidebarState');
    if (savedState === 'collapsed') {
        toggleSidebar();
    }

    // Handle mobile view
    function handleMobileView() {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('show');
            if (mainContent) mainContent.classList.remove('expanded');
            if (footer) footer.classList.remove('expanded');
        }
    }

    window.addEventListener('resize', handleMobileView);
    handleMobileView();

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    // Handle notifications
    function loadNotifications() {
        fetch('check_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notificationLink = document.querySelector('a[href="notifications.php"]');
                    if (notificationLink) {
                        // Get or create badge
                        let badge = notificationLink.querySelector('.notification-badge');
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'notification-badge';
                            notificationLink.appendChild(badge);
                        }

                        // Update badge
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
            });
    }

    // Load notifications initially and every 30 seconds
    loadNotifications();
    setInterval(loadNotifications, 30000);

    // Update notification badge position based on sidebar state
    function updateNotificationBadgePosition() {
        const badges = document.querySelectorAll('.notification-badge');
        const isCollapsed = sidebar.classList.contains('collapsed');

        badges.forEach(badge => {
            if (isCollapsed) {
                badge.style.right = '-5px';
                badge.style.top = '0';
            } else {
                badge.style.right = '10px';
                badge.style.top = '50%';
                badge.style.transform = 'translateY(-50%)';
            }
        });
    }

    // Call initially and on sidebar toggle
    updateNotificationBadgePosition();
    sidebar.addEventListener('transitionend', updateNotificationBadgePosition);
});