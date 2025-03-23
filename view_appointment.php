<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: client_dashboard.php');
    exit;
}

try {
    // Get appointment details
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            s.name as stylist_name,
            s.phone as stylist_phone
        FROM appointment a
        LEFT JOIN stylists s ON a.stylist_id = s.id
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        $_SESSION['error_message'] = "Appointment not found";
        header('Location: client_dashboard.php');
        exit;
    }

    // Get services for this appointment
    $stmt = $conn->prepare("
        SELECT 
            s.name,
            s.duration,
            as_rel.price
        FROM appointment_services as_rel
        JOIN services s ON as_rel.service_id = s.id
        WHERE as_rel.appointment_id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: client_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointment - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Appointment Details</h3>
                <a href="client_dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                <!-- Appointment Status -->
                <?php
                // Get status class based on appointment status
                $statusClass = 'secondary'; // default value
                switch($appointment['status']) {
                    case 'pending':
                        $statusClass = 'warning';
                        break;
                    case 'confirmed':
                        $statusClass = 'info';
                        break;
                    case 'completed':
                        $statusClass = 'success';
                        break;
                    case 'cancelled':
                        $statusClass = 'danger';
                        break;
                    case 'no_show':
                        $statusClass = 'dark';
                        break;
                }
                ?>
                <div class="alert alert-<?php echo $statusClass; ?> mb-4">
                    <strong>Status:</strong> <?php echo ucfirst($appointment['status']); ?>
                </div>

                <!-- Date and Time -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Date & Time</h5>
                        <p>
                            <?php 
                            echo date('F d, Y', strtotime($appointment['appointment_date'])) . '<br>';
                            echo date('h:i A', strtotime($appointment['appointment_time']));
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5>Stylist</h5>
                        <p>
                            <?php 
                            if ($appointment['stylist_name']) {
                                echo htmlspecialchars($appointment['stylist_name']) . '<br>';
                                echo '<small class="text-muted">' . 
                                     htmlspecialchars($appointment['stylist_phone']) . 
                                     '</small>';
                            } else {
                                echo 'Not assigned';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Services -->
                <h5>Booked Services</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Duration</th>
                            <th class="text-end">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalPrice = 0;
                        foreach ($services as $service): 
                            $totalPrice += $service['price'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                <td><?php echo $service['duration']; ?> mins</td>
                                <td class="text-end">
                                    $<?php echo number_format($service['price'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-info">
                            <th colspan="2">Total</th>
                            <th class="text-end">
                                $<?php echo number_format($totalPrice, 2); ?>
                            </th>
                        </tr>
                    </tbody>
                </table>

                <!-- Special Instructions -->
                <?php if (!empty($appointment['special_instructions'])): ?>
                    <div class="mt-4">
                        <h5>Special Instructions</h5>
                        <p class="border p-3 rounded bg-light">
                            <?php echo nl2br(htmlspecialchars($appointment['special_instructions'])); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="mt-4">
                    <?php if ($appointment['status'] == 'pending'): ?>
                        <a href="cancel_appointment.php?id=<?php echo $appointment['id']; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to cancel this appointment?');">
                            Cancel Appointment
                        </a>
                    <?php endif; ?>

                    <?php if ($appointment['status'] == 'completed'): ?>
                        <a href="feedback.php?appointment=<?php echo $appointment['id']; ?>" 
                           class="btn btn-primary">
                            Leave Feedback
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>