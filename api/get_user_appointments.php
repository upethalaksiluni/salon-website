<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    http_response_code(401);
    exit('Unauthorized or invalid request');
}

try {
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            GROUP_CONCAT(s.name) as services,
            f.rating,
            f.comments
        FROM appointment a
        LEFT JOIN appointment_services aps ON a.id = aps.appointment_id
        LEFT JOIN services s ON aps.service_id = s.id
        LEFT JOIN feedback f ON a.id = f.appointment_id
        WHERE a.user_id = ?
        GROUP BY a.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    
    $stmt->execute([$_GET['id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($appointments);
    
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database error: ' . $e->getMessage());
}