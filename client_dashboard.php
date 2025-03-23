<?php
session_start();
require_once 'includes/helpers.php';
require_once 'includes/notification_helpers.php'; // Add this line
$pageTitle = "Client Dashboard";
$activePage = "dashboard";
include "db_connect.php";
include "notification_handler.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Add this at the start of your PHP files that use the header
if (!isset($userProfile)) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
}

try {
    // Initialize notification handler
    $notificationHandler = new NotificationHandler($conn);
    
    // Get recent notifications
    $notifications = $notificationHandler->getUserNotifications(
        'client', 
        $_SESSION['user_id'], 
        5
    );
    
    // Get unread count
    $unreadCount = $notificationHandler->getUnreadCount(
        'client', 
        $_SESSION['user_id']
    );

    // Get user's upcoming appointments
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            a.total_amount,
            GROUP_CONCAT(s.name SEPARATOR ', ') as services,
            st.name as stylist_name
        FROM appointment a
        LEFT JOIN appointment_services as_rel ON a.id = as_rel.appointment_id
        LEFT JOIN services s ON as_rel.service_id = s.id
        LEFT JOIN stylists st ON a.stylist_id = st.id
        WHERE a.user_id = ? AND a.appointment_date >= CURRENT_DATE
        GROUP BY a.id
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $upcomingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMsg = "Error: " . $e->getMessage();
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
    <link rel="stylesheet" href="assets/css/variables.css">  <!-- Make sure this path is correct -->
    <link rel="stylesheet" href="assets/css/client_header.css">
    <link rel="stylesheet" href="assets/css/client-sidebar.css">
    <link rel="stylesheet" href="assets/css/client-footer.css">
    <link rel="stylesheet" href="assets/css/client_notifications.css">
    <style>
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 30px;
            margin-top: 130px;
        }

        .container {
            margin-bottom: 30px;
        }

        .quick-action-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .icon-wrapper {
            height: 70px;
            width: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .icon-wrapper i {
            font-size: 2rem;
            color: white;
        }

        .appointments-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .main-content {
            min-height: calc(100vh - 200px);
            padding: 2rem 0;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'client_header.dat'; ?>
    <?php include 'client_sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'client_header.dat'; ?>

        <div class="container">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2">Welcome back, <?php echo htmlspecialchars($userProfile['fullname']); ?>!</h1>
                        <p class="mb-0">Manage your appointments and explore our services</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="book_appointment.php" class="btn btn-light btn-lg">
                            <i class="fas fa-calendar-plus"></i> Book New Appointment
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card quick-action-card h-100">
                        <div class="card-body text-center">
                            <div class="icon-wrapper bg-primary mb-3">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <h5 class="card-title">Book Services</h5>
                            <p class="card-text">Schedule your next appointment with us</p>
                            <a href="book_services.php" class="btn btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card quick-action-card h-100">
                        <div class="card-body text-center">
                            <div class="icon-wrapper bg-success mb-3">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h5 class="card-title">My Appointments</h5>
                            <p class="card-text">View and manage your appointments</p>
                            <a href="my_appointments.php" class="btn btn-success">View All</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card quick-action-card h-100">
                        <div class="card-body text-center">
                            <div class="icon-wrapper bg-warning mb-3">
                                <i class="fas fa-star"></i>
                            </div>
                            <h5 class="card-title">Give Feedback</h5>
                            <p class="card-text">Share your experience with us</p>
                            <a href="my_appointments.php?feedback=true" class="btn btn-warning">Rate Services</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card quick-action-card h-100">
                        <div class="card-body text-center">
                            <div class="icon-wrapper bg-info mb-3">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h5 class="card-title">My Feedbacks</h5>
                            <p class="card-text">View your previous feedback history</p>
                            <a href="my_feedbacks.php" class="btn btn-info">View Feedbacks</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                

                <!-- Upcoming Appointments Column -->
                <div class="col-md-8">
                    <!-- Upcoming Appointments -->
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="mb-0"><i class="fas fa-calendar-check text-primary"></i> Upcoming Appointments</h5>
                            <a href="my_appointments.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($upcomingAppointments)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover appointments-table">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Services</th>
                                                <th>Stylist</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($upcomingAppointments as $appointment): ?>
                                                <tr>
                                                    <td>
                                                        <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>
                                                        </small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($appointment['services']); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['stylist_name'] ?? 'Not assigned'); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            $status_class = '';
                                                            switch($appointment['status']) {
                                                                case 'pending':
                                                                    $status_class = 'warning';
                                                                    break;
                                                                case 'confirmed':
                                                                    $status_class = 'success';
                                                                    break;
                                                                case 'cancelled':
                                                                    $status_class = 'danger';
                                                                    break;
                                                                case 'completed':
                                                                    $status_class = 'info';
                                                                    break;
                                                                case 'no_show':
                                                                    $status_class = 'dark';
                                                                    break;
                                                                default:
                                                                    $status_class = 'secondary';
                                                            }
                                                            echo $status_class;
                                                        ?>">
                                                            <?php echo ucfirst($appointment['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="view_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                                               class="btn btn-outline-primary">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <?php if ($appointment['status'] === 'pending'): ?>
                                                                <a href="cancel_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                                                   class="btn btn-outline-danger"
                                                                   onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                                                    <i class="fas fa-times"></i> Cancel
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if ($appointment['status'] === 'completed'): ?>
                                                                <a href="feedback.php?appointment=<?php echo $appointment['id']; ?>" 
                                                                   class="btn btn-outline-success">
                                                                    <i class="fas fa-star"></i> Rate
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <img src="assets/images/no-appointments.svg" alt="No appointments" class="mb-3" style="width: 150px;">
                                    <h5>No Upcoming Appointments</h5>
                                    <p class="text-muted mb-3">Ready to book your next appointment?</p>
                                    <a href="book_appointment.php" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus"></i> Book Now
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'client_footer.dat'; ?>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/client-sidebar.js"></script>
    <script src="assets/js/client_notifications.js"></script>
</body>
</html>