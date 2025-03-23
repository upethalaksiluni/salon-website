<?php
session_start();
$pageTitle = "Reports & Analytics";
$activePage = "reports";
$pageStyles = [
    'assets/css/admin_reports.css'
];
$pageScripts = [
    'https://cdn.jsdelivr.net/npm/chart.js',
    'assets/js/admin_reports.js'
];
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

try {
    // Get revenue statistics
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN appointment_date = CURRENT_DATE THEN total_amount ELSE 0 END) as today_revenue,
            SUM(CASE WHEN appointment_date BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) AND CURRENT_DATE THEN total_amount ELSE 0 END) as weekly_revenue,
            SUM(CASE WHEN MONTH(appointment_date) = MONTH(CURRENT_DATE) THEN total_amount ELSE 0 END) as monthly_revenue,
            COUNT(*) as total_appointments,
            AVG(total_amount) as average_transaction
        FROM appointment 
        WHERE status = 'completed'
    ");
    $stmt->execute();
    $revenueStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get service performance
    $stmt = $conn->prepare("
        SELECT 
            s.name,
            COUNT(*) as booking_count,
            SUM(as_rel.price) as total_revenue,
            AVG(f.rating) as avg_rating
        FROM services s
        LEFT JOIN appointment_services as_rel ON s.id = as_rel.service_id
        LEFT JOIN appointment a ON as_rel.appointment_id = a.id
        LEFT JOIN feedback f ON a.id = f.appointment_id
        WHERE a.status = 'completed'
        GROUP BY s.id
        ORDER BY booking_count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $servicePerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get stylist performance
    $stmt = $conn->prepare("
        SELECT 
            st.name,
            COUNT(a.id) as appointment_count,
            SUM(a.total_amount) as total_revenue,
            AVG(f.rating) as avg_rating
        FROM stylists st
        LEFT JOIN appointment a ON st.id = a.stylist_id
        LEFT JOIN feedback f ON a.id = f.appointment_id
        WHERE a.status = 'completed'
        GROUP BY st.id
        ORDER BY total_revenue DESC
    ");
    $stmt->execute();
    $stylistPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMsg = "Error fetching reports: " . $e->getMessage();
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
    <link href="assets/css/admin_header.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link href="assets/css/admin_header.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin_reports.css">
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
        <div class="main-content">
            <div class="content-box">
                <!-- Revenue Overview -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-success">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>$<?php echo number_format($revenueStats['today_revenue'], 2); ?></h3>
                                    <p>Today's Revenue</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-info">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>$<?php echo number_format($revenueStats['monthly_revenue'], 2); ?></h3>
                                    <p>Monthly Revenue</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-warning">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>$<?php echo number_format($revenueStats['average_transaction'], 2); ?></h3>
                                    <p>Avg Transaction</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-primary">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $revenueStats['total_appointments']; ?></h3>
                                    <p>Total Appointments</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Performance -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar text-primary"></i> Service Performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                        <th>Avg Rating</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($servicePerformance as $service): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                                            <td><?php echo $service['booking_count']; ?></td>
                                            <td>$<?php echo number_format($service['total_revenue'], 2); ?></td>
                                            <td>
                                                <?php 
                                                    $rating = round($service['avg_rating'], 1);
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        echo '<i class="fas fa-star ' . ($i <= $rating ? 'text-warning' : 'text-muted') . '"></i>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="progress">
                                                    <?php 
                                                        $performance = ($service['booking_count'] / max(array_column($servicePerformance, 'booking_count'))) * 100;
                                                    ?>
                                                    <div class="progress-bar bg-success" style="width: <?php echo $performance; ?>%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Stylist Performance -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user-check text-primary"></i> Stylist Performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Stylist</th>
                                        <th>Appointments</th>
                                        <th>Revenue Generated</th>
                                        <th>Avg Rating</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stylistPerformance as $stylist): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($stylist['name']); ?></td>
                                            <td><?php echo $stylist['appointment_count']; ?></td>
                                            <td>$<?php echo number_format($stylist['total_revenue'], 2); ?></td>
                                            <td>
                                                <?php 
                                                    $rating = round($stylist['avg_rating'], 1);
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        echo '<i class="fas fa-star ' . ($i <= $rating ? 'text-warning' : 'text-muted') . '"></i>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="progress">
                                                    <?php 
                                                        $performance = ($stylist['total_revenue'] / max(array_column($stylistPerformance, 'total_revenue'))) * 100;
                                                    ?>
                                                    <div class="progress-bar bg-info" style="width: <?php echo $performance; ?>%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.dat'; ?>

    
    <!-- Scripts -->
    <script src="assets/js/admin.js"></script>
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/admin_reports.js"></script>
</body>
</html>