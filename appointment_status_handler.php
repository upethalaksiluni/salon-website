<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

if (!isset($_SESSION['admin_id']) || !isset($_GET['action']) || !isset($_GET['id'])) {
    header('Location: admin_appointments.php');
    exit;
}

$action = $_GET['action'];
$appointmentId = $_GET['id'];

try {
    $conn->beginTransaction();

    // Update appointment status
    $status = ($action === 'approve') ? 'confirmed' : 'cancelled';
    $stmt = $conn->prepare("
        UPDATE appointment 
        SET status = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$status, $appointmentId]);

    // Get appointment details for notification
    $stmt = $conn->prepare("
        SELECT a.*, u.id as user_id
        FROM appointment a
        JOIN user u ON a.user_id = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$appointmentId]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create notification for user
    $notificationHandler = new NotificationHandler($conn);
    $title = "Appointment " . ucfirst($status);
    $message = "Your appointment for " . date('M d, Y', strtotime($appointment['appointment_date'])) . 
               " at " . date('h:i A', strtotime($appointment['appointment_time'])) . 
               " has been " . $status;

    $notificationHandler->createNotification(
        $appointment['user_id'],
        'client',
        'appointment_status',
        $title,
        $message,
        $appointmentId
    );

    $conn->commit();
    $_SESSION['success_message'] = "Appointment has been " . $status;

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Error updating appointment: " . $e->getMessage();
}

header('Location: admin_appointments.php');
exit;
?>