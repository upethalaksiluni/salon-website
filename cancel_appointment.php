<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: client_dashboard.php');
    exit;
}

try {
    $conn->beginTransaction();

    // Check if appointment belongs to user and is cancellable
    $stmt = $conn->prepare("
        SELECT status 
        FROM appointment 
        WHERE id = ? AND user_id = ? 
        AND status IN ('pending', 'confirmed')
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        $_SESSION['error_message'] = "Invalid appointment or cannot be cancelled";
        header('Location: client_dashboard.php');
        exit;
    }

    // Update appointment status
    $stmt = $conn->prepare("
        UPDATE appointment 
        SET status = 'cancelled', 
            notes = CONCAT(COALESCE(notes, ''), '\nCancelled by client on ', NOW())
        WHERE id = ?
    ");
    $stmt->execute([$_GET['id']]);

    // Create notification for admin
    $notificationHandler = new NotificationHandler($conn);
    $notificationHandler->createNotification(
        $_SESSION['user_id'],
        'admin',
        'status_update',
        'Appointment Cancelled',
        'Appointment #' . $_GET['id'] . ' has been cancelled by the client.',
        $_GET['id']
    );

    $conn->commit();
    $_SESSION['success_message'] = "Appointment cancelled successfully";

} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Error cancelling appointment: " . $e->getMessage();
}

header('Location: client_dashboard.php');
exit;
?>