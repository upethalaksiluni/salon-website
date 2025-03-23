<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'User ID required']));
}

try {
    $stmt = $conn->prepare("
        SELECT 
            u.*,
            COUNT(DISTINCT a.id) as total_appointments,
            COUNT(DISTINCT f.id) as total_feedback,
            ROUND(AVG(f.rating), 1) as avg_rating,
            (
                SELECT GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ')
                FROM appointment_services aps
                JOIN services s ON aps.service_id = s.id
                JOIN appointment a2 ON aps.appointment_id = a2.id
                WHERE a2.user_id = u.id
                LIMIT 5
            ) as frequent_services
        FROM user u
        LEFT JOIN appointment a ON u.id = a.user_id
        LEFT JOIN feedback f ON a.id = f.appointment_id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Convert null ratings to 'N/A'
    $user['avg_rating'] = $user['avg_rating'] ?? 'N/A';

    // Get recent appointments
    $stmt = $conn->prepare("
        SELECT 
            a.appointment_date,
            a.appointment_time,
            a.status,
            GROUP_CONCAT(s.name SEPARATOR ', ') as services
        FROM appointment a
        LEFT JOIN appointment_services aps ON a.id = aps.appointment_id
        LEFT JOIN services s ON aps.service_id = s.id
        WHERE a.user_id = ?
        GROUP BY a.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 5
    ");
    
    $stmt->execute([$_GET['id']]);
    $recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'user' => $user,
        'recent_appointments' => $recent_appointments
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}