<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_appointment_id'])) {
    header('Location: client_dashboard.php');
    exit;
}

$appointmentId = $_SESSION['last_appointment_id'];

try {
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            u.fullname,
            GROUP_CONCAT(s.name) as services
        FROM appointment a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN appointment_services as_rel ON a.id = as_rel.appointment_id
        LEFT JOIN services s ON as_rel.service_id = s.id
        WHERE a.id = ? AND a.user_id = ?
        GROUP BY a.id
    ");
    $stmt->execute([$appointmentId, $_SESSION['user_id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        header('Location: client_dashboard.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Error fetching appointment: " . $e->getMessage());
    header('Location: client_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmed - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
<div class="container mt-5 text-center">
        <div class="success-checkmark">
            <div class="check-icon">
                <span class="icon-line line-tip"></span>
                <span class="icon-line line-long"></span>
            </div>
        </div>
        
        <h1 class="mb-4">Thank You!</h1>
        <p class="lead mb-4">Your appointment request has been received.</p>
        <p class="mb-4">We will review your request and send you a confirmation shortly.</p>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">After Your Appointment</h5>
                        <p class="card-text">We value your feedback! Please don't forget to share your experience with us.</p>
                        <a href="feedback.php?appointment=<?php echo $appointmentId; ?>" 
                           class="btn btn-primary disabled feedback-btn" id="feedbackBtn">
                            Give Feedback
                        </a>
                        <p class="text-muted mt-2"><small>The feedback button will be enabled after your appointment.</small></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="client_dashboard.php" class="btn btn-outline-primary">Return to Dashboard</a>
        </div>
    </div>

    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>