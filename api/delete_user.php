<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if (!isset($_POST['id'])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'User ID required']));
}

try {
    $conn->beginTransaction();
    
    // Delete related records first
    $stmt = $conn->prepare("DELETE FROM feedback WHERE appointment_id IN (SELECT id FROM appointment WHERE user_id = ?)");
    $stmt->execute([$_POST['id']]);
    
    $stmt = $conn->prepare("DELETE FROM appointment_services WHERE appointment_id IN (SELECT id FROM appointment WHERE user_id = ?)");
    $stmt->execute([$_POST['id']]);
    
    $stmt = $conn->prepare("DELETE FROM appointment WHERE user_id = ?");
    $stmt->execute([$_POST['id']]);
    
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    
    $conn->commit();
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}