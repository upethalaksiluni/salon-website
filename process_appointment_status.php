<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
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

    $statusNote = date('Y-m-d H:i:s') . " - Status updated to " . $status;
    if ($notes) {
        $statusNote .= "\nNotes: " . $notes;
    }

    $stmt->execute([$status, $statusNote, $appointmentId]);

    // Get appointment details for notification
    $stmt = $conn->prepare("
        SELECT a.*, u.id as user_id 
        FROM appointment a
        JOIN user u ON a.user_id = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$appointmentId]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create notification for client
    $notificationHandler = new NotificationHandler($conn);
    $title = "Appointment Status Update";
    $content = "Your appointment has been " . strtolower($status);
    
    $notificationHandler->createNotification(
        $appointment['user_id'],
        'client',
        'status_update',
        $title,
        $content,
        $appointmentId
    );

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>