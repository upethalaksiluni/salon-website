<?php
session_start();
include "db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM appointment 
        WHERE appointment_date = ? 
        AND appointment_time = ?
        AND status NOT IN ('cancelled', 'completed')
    ");
    
    $stmt->execute([
        $data['date'],
        $data['time']
    ]);
    
    $count = $stmt->fetchColumn();
    
    // Check if stylist is available if specified
    $stylistAvailable = true;
    if (!empty($data['stylist_id'])) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM appointment 
            WHERE appointment_date = ? 
            AND appointment_time = ?
            AND stylist_id = ?
            AND status NOT IN ('cancelled', 'completed')
        ");
        $stmt->execute([
            $data['date'],
            $data['time'],
            $data['stylist_id']
        ]);
        $stylistCount = $stmt->fetchColumn();
        $stylistAvailable = $stylistCount == 0;
    }
    
    echo json_encode([
        'available' => $count < 3 && $stylistAvailable,
        'message' => $stylistAvailable ? '' : 'Selected stylist is not available at this time'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error checking availability: ' . $e->getMessage()
    ]);
}
?>