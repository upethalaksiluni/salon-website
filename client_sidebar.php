<?php
if (!isset($activePage)) {
    $activePage = '';
}
?>
<div class="client-sidebar <?php echo isset($_COOKIE['sidebarCollapsed']) && $_COOKIE['sidebarCollapsed'] === 'true' ? 'collapsed' : ''; ?>" id="clientSidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn" type="button">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <ul class="sidebar-menu">
        <li class="<?php echo $activePage == 'dashboard' ? 'active' : ''; ?>">
            <a href="client_dashboard.php" title="Dashboard">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?php echo $activePage == 'book' ? 'active' : ''; ?>">
            <a href="book_services.php" class="nav-item">
                <i class="fas fa-calendar-plus"></i>
                <span>Book Appointment</span>
            </a>
        </li>
        <li class="<?php echo $activePage == 'appointments' ? 'active' : ''; ?>">
            <a href="my_appointments.php">
                <i class="fas fa-calendar-check"></i>
                <span>My Appointments</span>
            </a>
        </li>
        <li class="<?php echo $activePage == 'services' ? 'active' : ''; ?>">
            <a href="my_services.php">
                <i class="fas fa-spa"></i>
                <span>Our Services</span>
            </a>
        </li>
        <li class="<?php echo $activePage == 'feedbacks' ? 'active' : ''; ?>">
            <a href="my_feedbacks.php">
                <i class="fas fa-star"></i>
                <span>My Feedbacks</span>
            </a>
        </li>
        <li class="<?php echo $activePage == 'notifications' ? 'active' : ''; ?>">
            <a href="notifications.php">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
        </li>
        <li class="<?php echo $activePage == 'profile' ? 'active' : ''; ?>">
            <a href="view_profile.php">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </li>
    </ul>
</div>