<?php
session_start();
require_once 'includes/notification_helpers.php';
$pageTitle = "My Appointments";
$activePage = "appointments";
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function hasFeedback($conn, $appointmentId) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM feedback 
        WHERE appointment_id = ?
    ");
    $stmt->execute([$appointmentId]);
    return $stmt->fetchColumn() > 0;
}

try {
    // Get user's appointments
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
        WHERE a.user_id = ?
        GROUP BY a.id, a.appointment_date, a.appointment_time, a.status, 
                 a.total_amount, st.name
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/client-header.css">
    <link rel="stylesheet" href="assets/css/client-sidebar.css">
    <link rel="stylesheet" href="assets/css/client-footer.css">
    <link rel="stylesheet" href="assets/css/appointments.css">

    <style>
        .main-content {
    padding: 2rem 0;
    margin-bottom: 30px;
    margin-top: 130px;
}

.appointment-card {
    transition: var(--transition);
    border: none;
    box-shadow: var(--box-shadow);
}

.appointment-card:hover {
    transform: var(--hover-transform);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.appointment-card .card-header {
    background: var(--primary-gradient);
    color: white;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    padding: 1rem 1.5rem;
}

.appointment-card .badge {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 20px;
}

.appointment-card .card-body {
    padding: 1.5rem;
}

.appointment-card .card-footer {
    background: none;
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.appointment-card i {
    width: 20px;
    text-align: center;
    margin-right: 0.5rem;
    color: var(--primary-color);
}

.btn-action {
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    font-weight: 500;
    transition: var(--transition);
}

.btn-action:hover {
    transform: var(--hover-transform);
}
    </style>
</head>
<body>
    <?php include 'client_header.dat'; ?>
    <?php include 'client_sidebar.php'; ?>

    <div class="main-content">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Appointments</h2>
                <a href="book_services.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Book New Appointment
                </a>
            </div>

            <?php if (!empty($appointments)): ?>
                <div class="row">
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card appointment-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?>
                                    </h5>
                                    <span class="badge bg-<?php 
                                        switch($appointment['status']) {
                                            case 'pending':
                                                echo 'warning';
                                                break;
                                            case 'confirmed':
                                                echo 'info';
                                                break;
                                            case 'completed':
                                                echo 'success';
                                                break;
                                            case 'cancelled':
                                                echo 'danger';
                                                break;
                                            case 'no_show':
                                                echo 'dark';
                                                break;
                                            default:
                                                echo 'secondary';
                                        } ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-2">
                                        <i class="far fa-clock"></i> 
                                        <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Services:</strong><br>
                                        <?php echo htmlspecialchars($appointment['services']); ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Stylist:</strong><br>
                                        <?php echo htmlspecialchars($appointment['stylist_name'] ?? 'Not assigned'); ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Total Amount:</strong><br>
                                        $<?php echo number_format($appointment['total_amount'], 2); ?>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <?php if ($appointment['status'] === 'confirmed'): ?>
                                        <a href="mark_done.php?id=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-success btn-sm w-100"
                                           onclick="return confirm('Mark this appointment as done?')">
                                            <i class="fas fa-check"></i> Done
                                        </a>
                                    <?php elseif ($appointment['status'] === 'completed' && !hasFeedback($conn, $appointment['id'])): ?>
                                        <a href="feedback.php?appointment=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-star"></i> Give Feedback
                                        </a>
                                    <?php elseif ($appointment['status'] === 'pending'): ?>
                                        <a href="cancel_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-danger btn-sm w-100"
                                           onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                            <i class="fas fa-times"></i> Cancel Appointment
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>No Appointments Found</h3>
                    <p>You haven't booked any appointments yet.</p>
                    <a href="book_services.php" class="btn btn-primary">
                        Book Your First Appointment
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php include 'client_footer.dat'; ?>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/client-sidebar.js"></script>
</body>
</html>