<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

// Check user authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Validate form data
if (empty($_POST['selected_services']) || empty($_POST['appointment_date']) || empty($_POST['appointment_time'])) {
    $_SESSION['error_message'] = 'Missing required fields';
    header('Location: book_appointment.php');
    exit;
}

try {
    $conn->beginTransaction();

    // Calculate totals from selected services
    $serviceIds = $_POST['selected_services'];
    $stmt = $conn->prepare("
        SELECT SUM(duration) as total_duration, SUM(price) as total_amount
        FROM services 
        WHERE id IN (" . str_repeat('?,', count($serviceIds) - 1) . "?)
    ");
    $stmt->execute($serviceIds);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Insert appointment
    $stmt = $conn->prepare("
        INSERT INTO appointment (
            user_id, 
            appointment_date, 
            appointment_time, 
            stylist_id, 
            special_instructions,
            total_duration,
            total_amount,
            status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['appointment_date'],
        $_POST['appointment_time'],
        !empty($_POST['stylist_id']) ? $_POST['stylist_id'] : null,
        $_POST['special_instructions'] ?? '',
        $totals['total_duration'],
        $totals['total_amount']
    ]);

    $appointmentId = $conn->lastInsertId();

    // Insert appointment services
    $stmt = $conn->prepare("
        INSERT INTO appointment_services (appointment_id, service_id, price)
        VALUES (?, ?, ?)
    ");

    foreach ($serviceIds as $serviceId) {
        // Get service price
        $priceStmt = $conn->prepare("SELECT price FROM services WHERE id = ?");
        $priceStmt->execute([$serviceId]);
        $price = $priceStmt->fetchColumn();
        
        $stmt->execute([$appointmentId, $serviceId, $price]);
    }

    // Create notification for admin
    $notificationHandler = new NotificationHandler($conn);
    $notificationHandler->createNotification(
        $_SESSION['user_id'],
        'admin',
        'new_appointment',
        'New Appointment Request',
        'New appointment booked for ' . $_POST['appointment_date'],
        $appointmentId
    );

    $conn->commit();
    
    $_SESSION['last_appointment_id'] = $appointmentId;
    header('Location: appointment_thank_you.php?id=' . $appointmentId);
    exit;

} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Booking error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error booking appointment. Please try again.";
    header('Location: book_appointment.php');
    exit;
}
?>