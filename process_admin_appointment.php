<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$appointmentId = $_POST['appointment_id'] ?? null;
$status = $_POST['status'] ?? null;
$notes = $_POST['notes'] ?? '';

if (!$appointmentId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $conn->beginTransaction();

    // Update appointment status
    $stmt = $conn->prepare("
        UPDATE appointment 
        SET status = ?, 
            notes = CONCAT(COALESCE(notes,''), '\n', ?),
            updated_at = NOW()
        WHERE id = ?
    ");

    $statusNote = date('Y-m-d H:i:s') . " - Status updated to " . $status . " by admin";
    if ($notes) {
        $statusNote .= "\nNotes: " . $notes;
    }

    $stmt->execute([$status, $statusNote, $appointmentId]);

    // Get appointment details for notification
    $stmt = $conn->prepare("
        SELECT a.*, u.id as user_id, u.email as user_email
        FROM appointment a
        JOIN user u ON a.user_id = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$appointmentId]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create notification for client
    $notificationHandler = new NotificationHandler($conn);
    $title = "Appointment " . ucfirst($status);
    $content = "Your appointment scheduled for " . 
               date('M d, Y', strtotime($appointment['appointment_date'])) . 
               " at " . date('h:i A', strtotime($appointment['appointment_time'])) . 
               " has been " . $status;

    $notificationHandler->createNotification(
        $appointment['user_id'],
        'client',
        'status_update',
        $title,
        $content,
        $appointmentId
    );

    // If appointment is confirmed, create reminder notification for admin
    if ($status === 'confirmed') {
        // Create reminder for day before
        $reminderDate = date('Y-m-d', strtotime($appointment['appointment_date'] . ' -1 day'));
        $reminderTime = $appointment['appointment_time'];
        
        $stmt = $conn->prepare("
            INSERT INTO admin_reminders (
                appointment_id, 
                reminder_date, 
                reminder_time, 
                status
            ) VALUES (?, ?, ?, 'pending')
        ");
        $stmt->execute([$appointmentId, $reminderDate, $reminderTime]);
    }

    $conn->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Appointment ' . $status . ' successfully'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>