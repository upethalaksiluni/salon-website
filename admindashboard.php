<?php
session_start();
$pageTitle = "Admin Dashboard";
$activePage = "dashboard";
include "db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

try {
    // Get today's appointments count
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM appointment 
        WHERE DATE(appointment_date) = CURRENT_DATE
    ");
    $stmt->execute();
    $todayAppointments = $stmt->fetchColumn();

    // Get pending appointments count
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM appointment 
        WHERE status = 'pending'
    ");
    $stmt->execute();
    $pendingAppointments = $stmt->fetchColumn();

    // Get new appointments (last 24 hours)
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM appointment 
        WHERE created_at >= NOW() - INTERVAL 24 HOUR
    ");
    $stmt->execute();
    $newAppointments = $stmt->fetchColumn();

    // Get today's bookings amount
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM appointment 
        WHERE DATE(appointment_date) = CURRENT_DATE
        AND status NOT IN ('cancelled', 'no_show')
    ");
    $stmt->execute();
    $todayBookings = $stmt->fetchColumn();

    // Total users count
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM user 
        WHERE is_admin = 0
    ");
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();

    // Total services count
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM services
    ");
    $stmt->execute();
    $totalServices = $stmt->fetchColumn();

    // Unread notifications count
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM notifications n
        LEFT JOIN user u ON n.user_id = u.id
        WHERE n.receiver_type = 'admin' 
        AND n.is_read = 0
    ");
    $stmt->execute();
    $unreadNotifications = $stmt->fetchColumn();

    // Get service statistics
    $stmt = $conn->prepare("
        SELECT 
            s.name, 
            COUNT(*) as booking_count,
            AVG(s.price) as average_price,
            COUNT(CASE WHEN s.status = 'active' THEN 1 END) as active_count
        FROM services s
        LEFT JOIN appointment_services as_rel ON s.id = as_rel.service_id
        LEFT JOIN appointment a ON as_rel.appointment_id = a.id
        LEFT JOIN user u ON a.user_id = u.id
        GROUP BY s.id, s.name
        ORDER BY booking_count DESC
        LIMIT 1
    ");
    $stmt->execute();
    $serviceStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $mostPopularService = $serviceStats['name'] ?? 'N/A';
    $averageServicePrice = $serviceStats['average_price'] ?? 0;
    $totalActiveServices = $serviceStats['active_count'] ?? 0;

    // Get service categories for chart
    $stmt = $conn->prepare("
        SELECT 
            s.category,
            COUNT(as_rel.service_id) as count
        FROM services s
        LEFT JOIN appointment_services as_rel ON s.id = as_rel.service_id
        GROUP BY s.category
        ORDER BY count DESC
    ");
    $stmt->execute();
    $categoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $categoryLabels = array_column($categoryData, 'category');
    $categoryCounts = array_column($categoryData, 'count');

    // Get appointments by day of week
    $stmt = $conn->prepare("
        SELECT 
            DAYNAME(appointment_date) as day_name,
            COUNT(*) as count
        FROM appointment
        WHERE appointment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        GROUP BY DAYNAME(appointment_date)
        ORDER BY DAYOFWEEK(appointment_date)
    ");
    $stmt->execute();
    $appointmentsByDay = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent appointments
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            u.fullname as client_name,
            COALESCE(s.name, 'Not assigned') as stylist_name
        FROM appointment a
        JOIN user u ON a.user_id = u.id
        LEFT JOIN stylists s ON a.stylist_id = s.id
        WHERE a.appointment_date >= CURRENT_DATE
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 5
    ");
    $stmt->execute();
    $recentAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get feedback statistics
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_feedbacks,
            AVG(rating) as avg_rating,
            COUNT(CASE WHEN created_at >= NOW() - INTERVAL 24 HOUR THEN 1 END) as new_feedbacks
        FROM feedback
    ");
    $stmt->execute();
    $feedbackStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get appointments by day for the last 7 days
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(appointment_date, '%Y-%m-%d') as date,
            COUNT(*) as count
        FROM appointment
        WHERE appointment_date BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY) AND CURRENT_DATE
        GROUP BY DATE_FORMAT(appointment_date, '%Y-%m-%d')
        ORDER BY date ASC
    ");
    $stmt->execute();
    $appointmentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for the chart
    $chartLabels = [];
    $chartData = [];
    
    // Create an array with all dates in the last 7 days
    for($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chartLabels[] = date('M d', strtotime($date));
        $chartData[] = 0; // Default to 0 appointments
        
        // Update with actual count if exists
        foreach($appointmentStats as $stat) {
            if($stat['date'] === $date) {
                $chartData[6-$i] = (int)$stat['count'];
                break;
            }
        }
    }

} catch (PDOException $e) {
    $errorMsg = "Error fetching dashboard data: " . $e->getMessage();
    // Initialize default values
    $todayAppointments = 0;
    $pendingAppointments = 0;
    $totalUsers = 0;
    $totalServices = 0;
    $unreadNotifications = 0;
    $mostPopularService = 'N/A';
    $averageServicePrice = 0;
    $totalActiveServices = 0;
    $categoryLabels = [];
    $categoryCounts = [];
    $recentAppointments = [];
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
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/admin_footer.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="assets/css/admin_header.css" rel="stylesheet">
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
        <div class="main-content">
            <div class="content-box">
                <!-- Stats Overview -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-primary">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $todayAppointments; ?></h3>
                                    <p>Today's Appointments</p>
                                </div>
                            </div>
                            <a href="admin_appointments.php?date=today" class="stat-link">View Details</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-success">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $pendingAppointments; ?></h3>
                                    <p>Pending Appointments</p>
                                </div>
                            </div>
                            <a href="admin_appointments.php?status=pending" class="stat-link">View Details</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-info">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $totalUsers; ?></h3>
                                    <p>Total Clients</p>
                                </div>
                            </div>
                            <a href="admin_users.php" class="stat-link">View Details</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-warning">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>$<?php echo number_format($todayBookings, 2); ?></h3>
                                    <p>Today's Revenue</p>
                                </div>
                            </div>
                            <a href="admin_reports.php" class="stat-link">View Report</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-info">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo number_format($feedbackStats['avg_rating'], 1); ?></h3>
                                    <p>Average Rating</p>
                                    <?php if ($feedbackStats['new_feedbacks'] > 0): ?>
                                        <span class="badge bg-warning"><?php echo $feedbackStats['new_feedbacks']; ?> new</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="admin_feedbacks.php" class="stat-link">View Feedbacks</a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Charts Section -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Appointment Trends</h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="appointmentTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-pie text-primary"></i> Service Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="serviceDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Appointments Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-check text-primary"></i> Recent Appointments</h5>
                        <a href="admin_appointments.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Client</th>
                                        <th>Stylist</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentAppointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['stylist_name'] ?? 'Not assigned'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    $status_class = '';
                                                    switch($appointment['status']) {
                                                        case 'scheduled':
                                                            $status_class = 'secondary';
                                                            break;
                                                        case 'confirmed':
                                                            $status_class = 'primary';
                                                            break;
                                                        case 'completed':
                                                            $status_class = 'success';
                                                            break;
                                                        case 'cancelled':
                                                            $status_class = 'danger';
                                                            break;
                                                        case 'no_show':
                                                            $status_class = 'warning';
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
                                                <a href="admin_appointments.php?view=<?php echo $appointment['id']; ?>" 
                                                class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($recentAppointments)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No recent appointments found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Feedbacks Section -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-comments text-primary"></i> Recent Feedbacks</h5>
                        <a href="admin_feedbacks.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $conn->prepare("
                            SELECT 
                                f.*,
                                u.fullname as client_name,
                                a.appointment_date
                            FROM feedback f
                            JOIN user u ON f.user_id = u.id
                            JOIN appointment a ON f.appointment_id = a.id
                            ORDER BY f.created_at DESC
                            LIMIT 5
                        ");
                        $stmt->execute();
                        $recentFeedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if (!empty($recentFeedbacks)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Rating</th>
                                            <th>Service Quality</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentFeedbacks as $feedback): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($feedback['client_name']); ?></td>
                                                <td>
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </td>
                                                <td><?php echo ucfirst($feedback['service_quality']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                                                <td>
                                                    <a href="view_feedback.php?id=<?php echo $feedback['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">No recent feedbacks</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.dat'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize chart instances as null
        let appointmentChart = null;
        let serviceChart = null;

        // Create appointment trends chart
        function initializeAppointmentChart() {
            const ctx = document.getElementById('appointmentTrendsChart');
            if (!ctx) return;

            // Destroy existing chart if it exists
            if (appointmentChart) {
                appointmentChart.destroy();
            }

            // Create new chart
            appointmentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($chartLabels); ?>,
                    datasets: [{
                        label: 'Appointments',
                        data: <?php echo json_encode($chartData); ?>,
                        fill: true,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgb(75, 192, 192)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return `Appointments: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Create service distribution chart
        function initializeServiceChart() {
            const ctx = document.getElementById('serviceDistributionChart');
            if (!ctx) return;

            // Destroy existing chart if it exists
            if (serviceChart) {
                serviceChart.destroy();
            }

            // Create new chart
            serviceChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($categoryLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($categoryCounts); ?>,
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        // Initialize both charts
        initializeAppointmentChart();
        initializeServiceChart();

        // Add window resize handler
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                initializeAppointmentChart();
                initializeServiceChart();
            }, 250);
        });
    });
    </script>
</body>
</html>