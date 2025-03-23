<?php
session_start();
include "db_connect.php";
include "notification_handler.php";
require_once 'includes/notification_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = "My Notifications";
$activePage = "notifications";

// Initialize notification handler
$notificationHandler = new NotificationHandler($conn);

try {
    // Get all notifications for the client
    $stmt = $conn->prepare("
        SELECT n.*, 
               a.appointment_date,
               a.appointment_time,
               s.name as service_name
        FROM notifications n
        LEFT JOIN appointment a ON n.related_id = a.id
        LEFT JOIN appointment_services as_rel ON a.id = as_rel.appointment_id
        LEFT JOIN services s ON as_rel.service_id = s.id
        WHERE n.user_id = ? 
        AND n.receiver_type = 'client'
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unread count
    $unreadCount = $notificationHandler->getUnreadCount('client', $_SESSION['user_id']);

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sulochana Salon</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/client-header.css">
    <link rel="stylesheet" href="assets/css/client-sidebar.css">
    <link rel="stylesheet" href="assets/css/client-footer.css">
    <link rel="stylesheet" href="assets/css/client_notifications.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <!-- Include Header -->
    <?php include 'client_header.dat'; ?>

    <div class="client-wrapper">
        <!-- Include Sidebar -->
        <?php include 'client_sidebar.php'; ?>

        <div class="main-content">
            <div class="container py-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>My Notifications</h2>
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-primary"><?php echo $unreadCount; ?> unread</span>
                    <?php endif; ?>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Notifications List -->
                <?php if (empty($notifications)): ?>
                    <div class="notification-empty">
                        <i class="fas fa-bell"></i>
                        <h3>No Notifications</h3>
                        <p>You don't have any notifications at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="notification-list">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>"
                                 data-notification-id="<?php echo $notification['id']; ?>">
                                <div class="notification-icon bg-<?php echo getNotificationColor($notification['notification_type']); ?>">
                                    <i class="fas fa-<?php echo getNotificationIcon($notification['notification_type']); ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-header">
                                        <h6 class="notification-title">
                                            <?php echo htmlspecialchars($notification['title']); ?>
                                        </h6>
                                        <span class="notification-time">
                                            <i class="far fa-clock"></i>
                                            <?php echo getTimeAgo($notification['created_at']); ?>
                                        </span>
                                    </div>
                                    <p class="notification-text">
                                        <?php echo htmlspecialchars($notification['content']); ?>
                                    </p>
                                    <?php if (!$notification['is_read']): ?>
                                        <div class="notification-actions">
                                            <button class="mark-read-btn" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                                Mark as read
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Include Footer -->
            <?php include 'client_footer.dat'; ?>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/client-sidebar.js"></script>
    <script src="assets/js/notifications.js"></script>
</body>
</html>