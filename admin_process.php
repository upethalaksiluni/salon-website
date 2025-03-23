<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include "db_connect.php";

// Handle admin login
if (isset($_POST['action']) && $_POST['action'] == 'admin_login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = 'Please enter both username and password';
        header('Location: adminlogin.php');
        exit;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT id, username, fullname 
            FROM admin 
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Development credentials check
        if ($username === 'Admin1' && $password === 'Admin@123') {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_username'] = 'Admin1';
            $_SESSION['admin_fullname'] = 'Administrator';
            header('Location: admindashboard.php');
            exit;
        } 
        else if ($username === 'Admin2' && $password === 'Admin@1234') {
            $_SESSION['admin_id'] = 2;
            $_SESSION['admin_username'] = 'Admin2';
            $_SESSION['admin_fullname'] = 'Administrator 2';
            header('Location: admindashboard.php');
            exit;
        }
        // Database credentials check
        else if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_fullname'] = $admin['fullname'];
            header('Location: admindashboard.php');
            exit;
        } 
        else {
            $_SESSION['error_message'] = 'Invalid username or password';
            header('Location: adminlogin.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Login failed: ' . $e->getMessage();
        header('Location: adminlogin.php');
        exit;
    }
}

// Handle admin logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Clear all session data
    session_unset();
    session_destroy();
    
    // Redirect to admin login
    header('Location: adminlogin.php');
    exit;
}

// Redirect to admin login if accessed directly
header('Location: adminlogin.php');
exit;
?>