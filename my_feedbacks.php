<?php
session_start();
require_once 'includes/notification_helpers.php';
$pageTitle = "My Feedbacks";
$activePage = "feedbacks";
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            f.*,
            a.appointment_date,
            a.appointment_time,
            GROUP_CONCAT(s.name) as services,
            st.name as stylist_name
        FROM feedback f
        JOIN appointment a ON f.appointment_id = a.id
        LEFT JOIN appointment_services as_rel ON a.id = as_rel.appointment_id
        LEFT JOIN services s ON as_rel.service_id = s.id
        LEFT JOIN stylists st ON a.stylist_id = st.id
        WHERE f.user_id = ?
        GROUP BY f.id
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="assets/css/feedback.css">

    <style>
        .feedback-card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
    margin-bottom: 20px;
}

.feedback-card:hover {
    transform: translateY(-5px);
}

.card-header {
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.rating-display {
    margin: 15px 0;
}

.main-content {
    padding: 2rem 0;
    margin-bottom: 30px;
    margin-top: 130px;
}

.rating-display label,
.service-quality label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #495057;
}

.stars {
    display: inline-block;
}

.stars .fa-star {
    color: #dee2e6;
    font-size: 1.2em;
    margin-right: 2px;
    transition: color 0.3s ease;
}

.stars .fa-star.active {
    color: #ffc107;
}

.badge {
    padding: 8px 12px;
    font-size: 0.9em;
}

.feedback-comment {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 15px;
    margin: 10px 0;
    border-radius: 4px;
    font-size: 0.95em;
    line-height: 1.5;
}

.appointment-details {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #dee2e6;
}

.appointment-details h6 {
    color: #007bff;
    margin-bottom: 15px;
    font-weight: 600;
}

.appointment-details ul li {
    margin-bottom: 10px;
    color: #6c757d;
}

.appointment-details i {
    width: 20px;
    color: #007bff;
    margin-right: 10px;
}

.alert i {
    margin-right: 8px;
}
    </style>
</head>
<body>
    <?php include 'client_header.dat'; ?>
    <?php include 'client_sidebar.php'; ?>

    <div class="main-content">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center margin-top-5px mb-4">
                <h2>My Feedbacks</h2>
                <a href="my_appointments.php" class="btn btn-outline-primary">
                    <i class="fas fa-calendar-check"></i> View Appointments
                </a>
            </div>

            <?php if (!empty($feedbacks)): ?>
                <div class="row">
                    <?php foreach ($feedbacks as $feedback): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card feedback-card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('F d, Y', strtotime($feedback['created_at'])); ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="rating-display mb-3">
                                        <label>Overall Rating:</label>
                                        <div class="stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'active' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="service-quality mb-3">
                                        <label>Service Quality:</label>
                                        <span class="badge <?php
                                            $quality = $feedback['service_quality'];
                                            $badgeClass = 'bg-secondary';
                                            
                                            switch($quality) {
                                                case 'excellent':
                                                    $badgeClass = 'bg-success';
                                                    break;
                                                case 'good':
                                                    $badgeClass = 'bg-primary';
                                                    break;
                                                case 'average':
                                                    $badgeClass = 'bg-warning';
                                                    break;
                                                case 'poor':
                                                    $badgeClass = 'bg-danger';
                                                    break;
                                            }
                                            echo $badgeClass;
                                        ?>">
                                             <?php echo ucfirst(htmlspecialchars($feedback['service_quality'])); ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($feedback['comments'])): ?>
                                        <div class="comments mb-3">
                                            <label>Comments:</label>
                                            <p class="feedback-comment"><?php echo nl2br(htmlspecialchars($feedback['comments'])); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="appointment-details">
                                        <h6>Appointment Details</h6>
                                        <ul class="list-unstyled">
                                            <li>
                                                <i class="far fa-calendar-alt"></i>
                                                <?php echo date('M d, Y h:i A', strtotime($feedback['appointment_date'] . ' ' . $feedback['appointment_time'])); ?>
                                            </li>
                                            <li>
                                                <i class="fas fa-cut"></i>
                                                <?php echo htmlspecialchars($feedback['services']); ?>
                                            </li>
                                            <li>
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($feedback['stylist_name'] ?? 'Not assigned'); ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-star"></i>
                    <h3>No Feedbacks Yet</h3>
                    <p>You haven't provided any feedback for your appointments.</p>
                    <a href="my_appointments.php" class="btn btn-primary">
                        View Completed Appointments
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