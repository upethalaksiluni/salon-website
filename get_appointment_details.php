<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

try {
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            u.fullname as client_name,
            u.email as client_email,
            u.phone as client_phone,
            s.name as stylist_name,
            GROUP_CONCAT(DISTINCT sv.name) as services,
            SUM(sv.price) as total_amount
        FROM appointment a
        JOIN user u ON a.user_id = u.id
        LEFT JOIN stylists s ON a.stylist_id = s.id
        LEFT JOIN appointment_services as_rel ON a.id = as_rel.appointment_id
        LEFT JOIN services sv ON as_rel.service_id = sv.id
        WHERE a.id = ?
        GROUP BY a.id
    ");
    
    $stmt->execute([$_GET['id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        exit('Appointment not found');
    }
?>

<link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
    <div class="appointment-details">
        <div class="row">
            <div class="col-md-6">
                <h5>Client Information</h5>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($appointment['client_name']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['client_phone']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($appointment['client_email']); ?></p>
            </div>
            <div class="col-md-6">
                <h5>Appointment Information</h5>
                <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($appointment['appointment_date'])); ?></p>
                <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                <p><strong>Stylist:</strong> <?php echo htmlspecialchars($appointment['stylist_name'] ?? 'Not assigned'); ?></p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h5>Booked Services</h5>
                <p><?php echo htmlspecialchars($appointment['services']); ?></p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($appointment['total_amount'], 2); ?></p>
            </div>
        </div>
        <?php if (!empty($appointment['special_instructions'])): ?>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h5>Special Instructions</h5>
                    <p><?php echo nl2br(htmlspecialchars($appointment['special_instructions'])); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php
} catch (PDOException $e) {
    error_log("Error fetching appointment details: " . $e->getMessage());
    exit('Error loading appointment details');
}
?>