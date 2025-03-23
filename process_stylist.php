<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

// Handle status toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status') {
    try {
        $stmt = $conn->prepare("
            UPDATE stylists 
            SET status = ?,
                updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_GET['status'],
            $_GET['id']
        ]);

        $_SESSION['success_message'] = "Stylist status updated successfully";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating stylist status: " . $e->getMessage();
    }
    
    header('Location: admin_stylists.php');
    exit;
}

try {
    // Handle GET requests (fetch stylist details)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM stylists WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $stylist = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stylist) {
                echo json_encode(['success' => true, 'stylist' => $stylist]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Stylist not found']);
            }
        }
        exit;
    }

    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if request is JSON (for status toggle)
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
        } else {
            $action = $_POST['action'] ?? '';
        }

        switch ($action) {
            case 'add':
                if (empty($_POST['name']) || empty($_POST['phone']) || empty($_POST['email'])) {
                    throw new Exception('Please fill all required fields');
                }

                $stmt = $conn->prepare("
                    INSERT INTO stylists (name, specialization, phone, email, status) 
                    VALUES (?, ?, ?, ?, 'active')
                ");
                
                $stmt->execute([
                    trim($_POST['name']),
                    trim($_POST['specialization']),
                    trim($_POST['phone']),
                    trim($_POST['email'])
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Stylist added successfully',
                    'stylist_id' => $conn->lastInsertId()
                ]);
                break;

            case 'edit':
                if (empty($_POST['stylist_id']) || empty($_POST['name']) || 
                    empty($_POST['phone']) || empty($_POST['email'])) {
                    throw new Exception('Please fill all required fields');
                }

                $stmt = $conn->prepare("
                    UPDATE stylists 
                    SET name = ?,
                        specialization = ?,
                        phone = ?,
                        email = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    trim($_POST['name']),
                    trim($_POST['specialization']),
                    trim($_POST['phone']),
                    trim($_POST['email']),
                    $_POST['stylist_id']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Stylist updated successfully'
                ]);
                break;

            case 'toggle_status':
                $stmt = $conn->prepare("
                    UPDATE stylists 
                    SET status = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $input['status'],
                    $input['stylist_id']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Status updated successfully'
                ]);
                break;

            default:
                throw new Exception('Invalid action');
        }
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>