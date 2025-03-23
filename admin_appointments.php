<?php
session_start();
$pageTitle = "Manage Appointments";
$activePage = "appointments";
$pageStyles = [
    'assets/css/admin_appointments.css'
];
$pageScripts = [
    'assets/js/admin_appointments.js'
];
include "db_connect.php";

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

// Status class helper function
function getStatusClass($status) {
    switch($status) {
        case 'pending': return 'warning';
        case 'confirmed': return 'info';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        case 'no_show': return 'dark';
        default: return 'secondary';
    }
}

// Get filter values
$period = $_GET['period'] ?? 'today';
$statusFilter = $_GET['status'] ?? 'all';
$stylistFilter = $_GET['stylist'] ?? 'all';

// Get date range based on period
function getDateRange($period) {
    $today = date('Y-m-d');
    switch($period) {
        case 'today': return [$today, $today];
        case 'tomorrow': 
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            return [$tomorrow, $tomorrow];
        case 'this_week':
            return [date('Y-m-d'), date('Y-m-d', strtotime('Sunday this week'))];
        case 'next_week':
            return [
                date('Y-m-d', strtotime('Monday next week')),
                date('Y-m-d', strtotime('Sunday next week'))
            ];
        case 'this_month':
            return [date('Y-m-d'), date('Y-m-t')];
        case 'next_month':
            return [
                date('Y-m-d', strtotime('first day of next month')),
                date('Y-m-t', strtotime('first day of next month'))
            ];
        default: return [$today, $today];
    }
}

[$startDate, $endDate] = getDateRange($period);

try {
    $params = [$startDate, $endDate];
    $sql = "
        SELECT 
            a.*,
            u.fullname as client_name,
            u.phone as client_phone,
            s.name as stylist_name,
            GROUP_CONCAT(sv.name) as services
        FROM appointment a
        JOIN user u ON a.user_id = u.id
        LEFT JOIN stylists s ON a.stylist_id = s.id
        LEFT JOIN appointment_services as_rel ON a.id = as_rel.appointment_id
        LEFT JOIN services sv ON as_rel.service_id = sv.id
        WHERE a.appointment_date BETWEEN ? AND ?
        GROUP BY a.id
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
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
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="assets/css/admin_footer.css" rel="stylesheet">
    <link href="assets/css/admin_header.css" rel="stylesheet">
    <link href="assets/css/admin_appointments.css" rel="stylesheet">
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>

        <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Book an appointment</h1>
            <div>
                <a href="admindashboard.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            </div>
        
        <div class="main-content">
            <div class="content-box">
                <!-- Filters Section -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Filter Appointments</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Time Period</label>
                                <select name="period" class="form-select" onchange="this.form.submit()">
                                    <option value="today" <?php echo $period === 'today' ? 'selected' : ''; ?>>Today</option>
                                    <option value="tomorrow" <?php echo $period === 'tomorrow' ? 'selected' : ''; ?>>Tomorrow</option>
                                    <option value="this_week" <?php echo $period === 'this_week' ? 'selected' : ''; ?>>This Week</option>
                                    <option value="next_week" <?php echo $period === 'next_week' ? 'selected' : ''; ?>>Next Week</option>
                                    <option value="this_month" <?php echo $period === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                                    <option value="next_month" <?php echo $period === 'next_month' ? 'selected' : ''; ?>>Next Month</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="all">All Status</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Appointments Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Appointments 
                            <span class="badge bg-primary ms-2"><?php echo count($appointments); ?></span>
                        </h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newAppointmentModal">
                            <i class="fas fa-plus"></i> New Appointment
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($appointments)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Client</th>
                                            <th>Services</th>
                                            <th>Stylist</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $appointment): ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                        echo date('M d, Y', strtotime($appointment['appointment_date'])) . '<br>';
                                                        echo date('h:i A', strtotime($appointment['appointment_time']));
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($appointment['client_name']); ?><br>
                                                    <small class="text-muted"><?php echo $appointment['client_phone']; ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($appointment['services']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['stylist_name'] ?? 'Not assigned'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusClass($appointment['status']); ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-info view-appointment" 
                                                                data-id="<?php echo $appointment['id']; ?>"
                                                                title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($appointment['status'] === 'pending'): ?>
                                                            <button type="button" class="btn btn-sm btn-success approve-appointment"
                                                                    data-id="<?php echo $appointment['id']; ?>"
                                                                    title="Approve">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger cancel-appointment"
                                                                    data-id="<?php echo $appointment['id']; ?>"
                                                                    title="Cancel">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted my-5">No appointments found for the selected period.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>


    <!-- Appointment Details Modal -->
    <div class="modal fade" id="appointmentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="appointmentDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'admin_footer.dat'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- For dashboard charts -->
    <script src="assets/js/admin_appointments.js"></script>
</body>
</html>