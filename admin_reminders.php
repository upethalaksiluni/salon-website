<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

try {
    // Get upcoming appointments with reminders
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            u.fullname as client_name,
            u.phone as client_phone,
            s.name as stylist_name,
            GROUP_CONCAT(sv.name) as services,
            ar.reminder_date
        FROM appointment a
        JOIN user u ON a.user_id = u.id
        LEFT JOIN stylists s ON a.stylist_id = s.id
        LEFT JOIN appointment_services as_rel ON a.id = as_rel.appointment_id
        LEFT JOIN services sv ON as_rel.service_id = sv.id
        LEFT JOIN admin_reminders ar ON a.id = ar.appointment_id
        WHERE a.status = 'confirmed' 
        AND a.appointment_date >= CURRENT_DATE
        GROUP BY a.id
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $stmt->execute();
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
    <title>Appointment Reminders - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin_reminders.css">
</head>
<body class="admin-body">
    <?php 
    $pageTitle = "Appointment Reminders";
    $activePage = "reminders";
    $pageStyles = [
        'assets/css/admin_reminders.css'
    ];
    $pageScripts = [
        'assets/js/admin_reminders.js'
    ];
    include 'admin_header.dat'; 
    ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Appointment Reminders</h1>
            <a href="admindashboard.php" class="btn btn-outline-secondary">
                Back to Dashboard
            </a>
        </div>

        <?php if (!empty($appointments)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Client</th>
                            <th>Services</th>
                            <th>Stylist</th>
                            <th>Reminder Status</th>
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
                                    <small><?php echo htmlspecialchars($appointment['client_phone']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($appointment['services']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['stylist_name'] ?? 'Not assigned'); ?></td>
                                <td>
                                    <?php if (strtotime($appointment['reminder_date']) === strtotime('tomorrow')): ?>
                                        <span class="badge bg-warning">Reminder Due</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Scheduled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary view-details" 
                                            data-id="<?php echo $appointment['id']; ?>">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No upcoming appointments found.</div>
        <?php endif; ?>
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
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.dat'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/admin.js"></script>
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/notifications.js" defer></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin_reminders.js"></script>
    <script>
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function() {
                const appointmentId = this.dataset.id;
                const modal = new bootstrap.Modal(document.getElementById('appointmentDetailsModal'));
                
                fetch(`get_appointment_details.php?id=${appointmentId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('appointmentDetailsContent').innerHTML = data;
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error loading appointment details');
                    });
            });
        });
    </script>
</body>
</html>