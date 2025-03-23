<?php
session_start();
include "db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $isAdmin = isset($_SESSION['admin_id']);
    $userId = $isAdmin ? $_SESSION['admin_id'] : $_SESSION['user_id'];
    $receiverType = $isAdmin ? 'admin' : 'client';

    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE receiver_type = ? 
        AND (user_id = ? OR (? AND user_id IS NULL))
        AND is_read = 0
    ");
    $stmt->execute([$receiverType, $userId, $isAdmin]);
    $count = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'count' => $count
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>