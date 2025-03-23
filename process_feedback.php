<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

if (!isset($_SESSION['user_id']) || empty($_POST['appointment_id'])) {
    header('Location: my_appointments.php');
    exit;
}

try {
    // Set MySQL-specific transaction isolation level
    $conn->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
    $conn->beginTransaction();

    // Insert feedback
    $stmt = $conn->prepare("
        INSERT INTO feedback (
            appointment_id,
            user_id,
            rating,
            service_quality,
            comments,
            created_at
        ) VALUES (?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $_POST['appointment_id'],
        $_SESSION['user_id'],
        $_POST['rating'],
        $_POST['service_quality'],
        $_POST['comments'] ?? null
    ]);

    // Get appointment and user details
    $stmt = $conn->prepare("
        SELECT a.*, u.fullname as client_name 
        FROM appointment a 
        JOIN user u ON a.user_id = u.id 
        WHERE a.id = ?
    ");
    $stmt->execute([$_POST['appointment_id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create notification for admin
    $notificationHandler = new NotificationHandler($conn);
    $notificationHandler->createNotification(
        $_SESSION['user_id'],
        'admin',
        'feedback',
        'New Feedback Received',
        "Client {$appointment['client_name']} has submitted feedback for their appointment.",
        $_POST['appointment_id']
    );

    $conn->commit();
    header('Location: feedback_thank_you.php');
    exit;

} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Error submitting feedback: " . $e->getMessage();
    header('Location: feedback.php?appointment=' . $_POST['appointment_id']);
    exit;
}
?>