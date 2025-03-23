<?php
session_start();
require_once 'includes/notification_helpers.php';
$pageTitle = "Notifications";
$activePage = "notifications";
include "db_connect.php";

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$isAdmin = isset($_SESSION['admin_id']);
$userId = $isAdmin ? $_SESSION['admin_id'] : $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all'; // all, read, unread

try {
    $sql = "SELECT n.*, 
            CASE 
                WHEN n.notification_type = 'new_appointment' THEN a.appointment_date
                ELSE NULL 
            END as appointment_date,
            u.fullname as sender_name
            FROM notifications n
            LEFT JOIN appointment a ON n.related_id = a.id 
            LEFT JOIN user u ON n.user_id = u.id
            WHERE n.receiver_type = ? ";
    
    $params = [$isAdmin ? 'admin' : 'client'];
    
    if (!$isAdmin) {
        $sql .= "AND n.user_id = ? ";
        $params[] = $userId;
    }
    
    if ($filter !== 'all') {
        $sql .= "AND n.is_read = ? ";
        $params[] = ($filter === 'read' ? 1 : 0);
    }
    
    $sql .= "ORDER BY n.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/client-header.css">
    <link rel="stylesheet" href="assets/css/client-sidebar.css">
    <link rel="stylesheet" href="assets/css/client-footer.css">
    
    <style>
        .main-content {
            padding: 2rem 0;
            margin-top: 80px;
        }

        .notification-card {
            transition: var(--transition);
            border: none;
            box-shadow: var(--box-shadow);
            margin-bottom: 1rem;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .notification-card:hover {
            transform: var(--hover-transform);
        }

        .notification-card.unread {
            border-left: 4px solid var(--primary-color);
        }

        .notification-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-body {
            padding: 1.5rem;
        }

        .notification-time {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .filter-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            transition: var(--transition);
        }

        .filter-btn:hover {
            transform: var(--hover-transform);
        }

        .filter-btn.active {
            background: var(--primary-gradient);
            color: white;
            border: none;
        }

        .mark-read-btn {
            padding: 0.4rem 1rem;
            border-radius: 15px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .mark-read-btn:hover {
            transform: var(--hover-transform);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include 'client_header.dat'; ?>
    <?php include 'client_sidebar.php'; ?>

    <div class="main-content">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Your Notifications</h2>
                <div class="btn-group">
                    <a href="?filter=all" class="btn filter-btn <?php echo $filter === 'all' ? 'active' : 'btn-outline-primary'; ?>">
                        All
                    </a>
                    <a href="?filter=unread" class="btn filter-btn <?php echo $filter === 'unread' ? 'active' : 'btn-outline-primary'; ?>">
                        Unread
                    </a>
                    <a href="?filter=read" class="btn filter-btn <?php echo $filter === 'read' ? 'active' : 'btn-outline-primary'; ?>">
                        Read
                    </a>
                </div>
            </div>

            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-card <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                        <div class="notification-header">
                            <h5 class="mb-0"><?php echo htmlspecialchars($notification['title']); ?></h5>
                            <span class="notification-time">
                                <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                            </span>
                        </div>
                        <div class="notification-body">
                            <p class="mb-3"><?php echo htmlspecialchars($notification['content']); ?></p>
                            <?php if (!$notification['is_read']): ?>
                                <button class="btn btn-primary mark-read-btn" 
                                        data-id="<?php echo $notification['id']; ?>">
                                    <i class="fas fa-check"></i> Mark as Read
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No Notifications Found</h3>
                    <p>You don't have any <?php echo $filter !== 'all' ? $filter : ''; ?> notifications at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'client_footer.dat'; ?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/client-sidebar.js"></script>
    <script>
        document.querySelectorAll('.mark-read-btn').forEach(button => {
            button.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                const card = this.closest('.notification-card');

                fetch('mark_notification_as_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: notificationId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        card.classList.remove('unread');
                        this.remove();
                        updateNotificationCount();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to mark notification as read');
                });
            });
        });

        function updateNotificationCount() {
            fetch('check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.querySelector('.notification-badge');
                        if (badge) {
                            if (data.count > 0) {
                                badge.textContent = data.count;
                                badge.style.display = 'flex';
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                    }
                })
                .catch(error => console.error('Error updating notifications:', error));
        }
    </script>
</body>
</html>