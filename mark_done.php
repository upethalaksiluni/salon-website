<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: my_appointments.php');
    exit;
}

try {
    $conn->beginTransaction();

    // Update appointment status to completed
    $stmt = $conn->prepare("
        UPDATE appointment 
        SET status = 'completed',
            updated_at = NOW()
        WHERE id = ? 
        AND user_id = ? 
        AND status = 'confirmed'
    ");
    
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        // Create notification for admin
        $notificationHandler = new NotificationHandler($conn);
        $notificationHandler->createNotification(
            $_SESSION['user_id'],
            'admin',
            'status_update',
            'Appointment Completed',
            'Client has marked appointment #' . $_GET['id'] . ' as completed.',
            $_GET['id']
        );

        $conn->commit();
        // Redirect to feedback page
        header('Location: feedback.php?appointment=' . $_GET['id']);
        exit;
    } else {
        throw new Exception("Unable to update appointment status");
    }

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: my_appointments.php');
    exit;
}
?>