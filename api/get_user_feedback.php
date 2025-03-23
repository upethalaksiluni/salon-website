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
            f.*,
            a.appointment_date,
            GROUP_CONCAT(s.name) as services,
            u.fullname,
            u.username
        FROM feedback f
        JOIN appointment a ON f.appointment_id = a.id
        JOIN user u ON a.user_id = u.id
        LEFT JOIN appointment_services aps ON a.id = aps.appointment_id
        LEFT JOIN services s ON aps.service_id = s.id
        WHERE a.user_id = ?
        GROUP BY f.id
        ORDER BY a.appointment_date DESC
    ");
    
    $stmt->execute([$_GET['id']]);
    $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($feedback);
    
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database error: ' . $e->getMessage());
}