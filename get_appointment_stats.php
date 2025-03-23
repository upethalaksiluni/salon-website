<?php
session_start();
include "db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Get appointments by day name for the last 7 days
    $stmt = $conn->prepare("
        SELECT 
            DAYNAME(appointment_date) as day_name,
            COUNT(*) as count
        FROM appointment
        WHERE appointment_date BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY) AND CURRENT_DATE
        GROUP BY DAYNAME(appointment_date)
        ORDER BY FIELD(
            DAYNAME(appointment_date), 
            'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
        )
    ");
    $stmt->execute();
    $appointmentsByDay = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize data for all days of the week
    $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $formattedData = array_fill_keys($daysOfWeek, 0);

    // Fill in actual counts
    foreach ($appointmentsByDay as $day) {
        $formattedData[$day['day_name']] = (int)$day['count'];
    }

    echo json_encode([
        'success' => true,
        'appointmentsByDay' => array_map(
            function($day_name) use ($formattedData) {
                return [
                    'day_name' => $day_name,
                    'count' => $formattedData[$day_name]
                ];
            },
            $daysOfWeek
        )
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>