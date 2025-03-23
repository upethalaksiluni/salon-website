<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing notification ID']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND (
            (receiver_type = 'client' AND user_id = ?) OR
            (receiver_type = 'admin' AND ? IN (SELECT id FROM admin))
        )
    ");

    $stmt->execute([
        $data['id'],
        $_SESSION['user_id'] ?? null,
        $_SESSION['admin_id'] ?? null
    ]);

    // Return success without redirecting
    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as read'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error marking notification as read'
    ]);
}
?>