<?php
session_start();
include "db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $_POST['action'] ?? $input['action'] ?? '';

        switch ($action) {
            case 'create':
                // Validate input
                if (empty($_POST['username']) || empty($_POST['password']) || 
                    empty($_POST['fullname']) || empty($_POST['email'])) {
                    throw new Exception("All fields are required");
                }

                // Check if username exists
                $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
                $stmt->execute([$_POST['username']]);
                if ($stmt->rowCount() > 0) {
                    throw new Exception("Username already exists");
                }

                // Insert new admin
                $stmt = $conn->prepare("
                    INSERT INTO admin (username, password, fullname, email, status)
                    VALUES (?, ?, ?, ?, 'active')
                ");
                
                $stmt->execute([
                    $_POST['username'],
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['fullname'],
                    $_POST['email']
                ]);

                // Log action
                logAdminAction($_SESSION['admin_id'], 'created_admin', "Created new admin: {$_POST['username']}");

                echo json_encode(['success' => true, 'message' => 'Admin created successfully']);
                break;

            case 'delete':
                if (empty($input['admin_id'])) {
                    throw new Exception("Admin ID is required");
                }

                // Check if trying to delete self
                if ($input['admin_id'] == $_SESSION['admin_id']) {
                    throw new Exception("You cannot delete your own account");
                }

                // Delete admin
                $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
                $stmt->execute([$input['admin_id']]);

                // Log action
                logAdminAction($_SESSION['admin_id'], 'deleted_admin', "Deleted admin ID: {$input['admin_id']}");

                echo json_encode(['success' => true, 'message' => 'Admin deleted successfully']);
                break;

            case 'update':
                if (empty($_POST['admin_id']) || empty($_POST['fullname']) || empty($_POST['email'])) {
                    throw new Exception("Required fields cannot be empty");
                }

                // Check if email exists for other admins
                $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
                $stmt->execute([$_POST['email'], $_POST['admin_id']]);
                if ($stmt->rowCount() > 0) {
                    throw new Exception("Email already exists");
                }

                // Update admin
                $stmt = $conn->prepare("
                    UPDATE admin 
                    SET fullname = ?, email = ?, phone = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");

                $stmt->execute([
                    $_POST['fullname'],
                    $_POST['email'],
                    $_POST['phone'] ?? null,
                    $_POST['status'],
                    $_POST['admin_id']
                ]);

                // Log action
                logAdminAction(
                    $_SESSION['admin_id'], 
                    'updated_admin', 
                    "Updated admin ID: {$_POST['admin_id']}"
                );

                echo json_encode(['success' => true, 'message' => 'Admin updated successfully']);
                break;

            default:
                throw new Exception("Invalid action");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Helper function to log admin actions
function logAdminAction($adminId, $action, $details) {
    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO admin_audit_log (admin_id, action, details)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$adminId, $action, $details]);
}
?>