<?php
// process_service.php
// Start session
session_start();

// Include database connection
include "db_connect.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? 'add';
        
        switch($action) {
            case 'edit':
                // Validate input
                if (empty($_POST['service_id']) || empty($_POST['name']) || 
                    empty($_POST['category']) || empty($_POST['duration']) || 
                    !isset($_POST['price'])) {
                    throw new Exception("All required fields must be filled out");
                }

                // Prepare update statement
                $stmt = $conn->prepare("
                    UPDATE services 
                    SET name = ?,
                        category = ?,
                        description = ?,
                        duration = ?,
                        price = ?,
                        status = ?
                    WHERE id = ?
                ");

                // Execute update
                $stmt->execute([
                    $_POST['name'],
                    $_POST['category'],
                    $_POST['description'],
                    $_POST['duration'],
                    $_POST['price'],
                    $_POST['status'],
                    $_POST['service_id']
                ]);

                $_SESSION['success_message'] = "Service updated successfully!";
                break;

            case 'delete':
                // Check if service can be deleted
                $stmt = $conn->prepare("
                    SELECT COUNT(*) FROM appointment_services 
                    WHERE service_id = ?
                ");
                $stmt->execute([$_POST['service_id']]);
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Cannot delete service with existing bookings");
                }

                // Delete service
                $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
                $stmt->execute([$_POST['service_id']]);

                $_SESSION['success_message'] = "Service deleted successfully!";
                break;

            case 'add':
                // Validate input
                if (empty($_POST['name']) || empty($_POST['category']) || 
                    empty($_POST['duration']) || !isset($_POST['price'])) {
                    throw new Exception("All required fields must be filled out");
                }

                // Insert new service
                $stmt = $conn->prepare("
                    INSERT INTO services (name, category, description, duration, price, status)
                    VALUES (?, ?, ?, ?, ?, 'active')
                ");

                $stmt->execute([
                    $_POST['name'],
                    $_POST['category'],
                    $_POST['description'],
                    $_POST['duration'],
                    $_POST['price']
                ]);

                $_SESSION['success_message'] = "Service added successfully!";
                break;
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Redirect back to admin services page
header('Location: admin_services.php');
exit;
?>