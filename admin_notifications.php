<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            n.*,
            u.name as user_name
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        WHERE n.receiver_type = 'admin'
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = "Error fetching notifications: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="assets/css/admin_header.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin_notifications.css">
</head>
<body class="admin-body">
    <?php
    $pageTitle = "Admin Notifications";
    $activePage = "notifications";
    $pageStyles = [
        'assets/css/admin_notifications.css'
    ];
    $pageScripts = [
        'assets/js/notifications.js'
    ];
    include 'admin_header.dat';
    ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
    <div class="container mt-5">
        <h1>Notifications</h1>
        
        <?php if (!empty($notifications)): ?>
            <div class="list-group">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item <?php echo $notification['is_read'] ? '' : 'list-group-item-primary'; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                            <small><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($notification['content']); ?></p>
                        <?php if ($notification['user_name']): ?>
                            <small>From: <?php echo htmlspecialchars($notification['user_name']); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No notifications found.</div>
        <?php endif; ?>
        
        <div class="mt-3">
            <a href="admindashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        </div>
        </div>

    <?php include 'admin_footer.dat'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/admin.js"></script>
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/notifications.js" defer></script>
</body>
</html>