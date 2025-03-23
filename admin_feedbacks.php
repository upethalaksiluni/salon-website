<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            f.*,
            u.fullname as client_name,
            a.appointment_date,
            a.appointment_time,
            GROUP_CONCAT(s.name) as services,
            st.name as stylist_name
        FROM feedback f
        JOIN appointment a ON f.appointment_id = a.id
        JOIN user u ON f.user_id = u.id
        LEFT JOIN appointment_services as_rel ON a.id = as_rel.appointment_id
        LEFT JOIN services s ON as_rel.service_id = s.id
        LEFT JOIN stylists st ON a.stylist_id = st.id
        GROUP BY f.id
        ORDER BY f.created_at DESC
    ");
    $stmt->execute();
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
    <title>Customer Feedbacks - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/admin_feedbacks.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <?php
    $pageTitle = "Customer Feedbacks";
    $activePage = "feedbacks";
    $pageStyles = [
        'assets/css/admin_feedbacks.css'
    ];
    $pageScripts = [
        'assets/js/admin_feedbacks.js'
    ];
    include 'admin_header.dat';
    ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Customer Feedbacks</h1>
            <a href="admindashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>

        <?php if (!empty($feedbacks)): ?>
            <div class="row">
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <?php echo htmlspecialchars($feedback['client_name']); ?>
                                    <small class="text-muted">
                                        (<?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>)
                                    </small>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Rating:</strong> 
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Service Quality:</strong> 
                                    <?php echo ucfirst($feedback['service_quality']); ?>
                                </div>
                                <?php if (!empty($feedback['comments'])): ?>
                                    <div class="mb-3">
                                        <strong>Comments:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($feedback['comments'])); ?>
                                    </div>
                                <?php endif; ?>
                                <hr>
                                <div class="small">
                                    <p class="mb-1"><strong>Appointment:</strong> 
                                        <?php echo date('M d, Y h:i A', strtotime($feedback['appointment_date'] . ' ' . $feedback['appointment_time'])); ?>
                                    </p>
                                    <p class="mb-1"><strong>Services:</strong> 
                                        <?php echo htmlspecialchars($feedback['services']); ?>
                                    </p>
                                    <p class="mb-0"><strong>Stylist:</strong> 
                                        <?php echo htmlspecialchars($feedback['stylist_name']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No feedbacks found.</div>
        <?php endif; ?>
        </div>
        </div>

    <?php include 'admin_footer.dat'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>