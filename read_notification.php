<?php
session_start();
include "db_connect.php";

if (!isset($_POST['notification_id'])) {
    header('Content-Type: application/json');
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
        $_POST['notification_id'],
        $_SESSION['user_id'] ?? null,
        $_SESSION['admin_id'] ?? null
    ]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>