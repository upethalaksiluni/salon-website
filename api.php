<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include dependencies
include "db_connect.php";
include "bin/cache_handler.php";

// Set response header
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Invalid request'];

// Handle admin login
if (isset($_POST['action']) && $_POST['action'] == 'admin_login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => 'admindashboard.php'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Login failed: ' . $e->getMessage()
        ]);
    }
}

// Handle login
else if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $response['message'] = 'Please enter both username and password';
        echo json_encode($response);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT id, username, password, fullname FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];  // Store id as user_id in session
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            
            // Update cache
            CacheHandler::set('user_'.$user['id'], $user, 3600); // Cache for 1 hour
            
            $response['success'] = true;
            $response['message'] = 'Login successful';
            $response['redirect'] = 'dashboard.php';
        } else {
            $response['message'] = 'Invalid username or password';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Login failed: ' . $e->getMessage();
    }
}

// Handle registration
else if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $requiredFields = ['fullname', 'gender', 'birthdate', 'phone', 'email', 'username', 'password'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $response['message'] = 'Please fill in all required fields: ' . implode(', ', $missingFields);
        echo json_encode($response);
        exit;
    }
    
    // Get form data
    $fullname = $_POST['fullname'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'] ?? '';
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $preferred_stylist = $_POST['preferred_stylist'] ?? '';
    $frequent_services = $_POST['frequent_services'] ?? '';
    $preferred_time = $_POST['preferred_time'] ?? '';
    $allergies = $_POST['allergies'] ?? '';
    $medical_conditions = $_POST['medical_conditions'] ?? '';
    
    // Handle profile image upload if provided
    $profile_image = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify file extension
        if (in_array(strtolower($filetype), $allowed)) {
            // Create unique filename
            $newFilename = uniqid() . '.' . $filetype;
            $uploadDir = 'uploads/profiles/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadPath = $uploadDir . $newFilename;
            
            // Upload file
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                $profile_image = $uploadPath;
            } else {
                $response['message'] = 'Failed to upload profile image';
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = 'Invalid file type. Allowed: ' . implode(', ', $allowed);
            echo json_encode($response);
            exit;
        }
    }
    
    // Check if username already exists
    try {
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $response['message'] = 'Username already exists';
            echo json_encode($response);
            exit;
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $response['message'] = 'Email already exists';
            echo json_encode($response);
            exit;
        }
        
        // Insert user data
        $stmt = $conn->prepare("
            INSERT INTO user (fullname, gender, birthdate, phone, email, address, 
                            username, password, preferred_stylist, frequent_services, 
                            preferred_time, allergies, medical_conditions, profile_image, 
                            created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $fullname, $gender, $birthdate, $phone, $email, $address, 
            $username, $password, $preferred_stylist, $frequent_services, 
            $preferred_time, $allergies, $medical_conditions, $profile_image
        ]);
        
        $userId = $conn->lastInsertId();
        
        if ($userId) {
            $response['success'] = true;
            $response['message'] = 'Registration successful. You can now login.';
        } else {
            $response['message'] = 'Registration failed';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Registration failed: ' . $e->getMessage();
    }
}

// Handle email verification for password reset
else if (isset($_POST['action']) && $_POST['action'] == 'verify_email') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $response['message'] = 'Please enter a valid email';
        echo json_encode($response);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT id, fullname FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Generate verification code
            $verificationCode = rand(100000, 999999);
            
            // Store code in session for verification
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_code'] = $verificationCode;
            $_SESSION['reset_time'] = time();
            
            // In a real application, send email with verification code
            // For demonstration, we'll just "pretend" it was sent
            
            $response['success'] = true;
            $response['message'] = 'Verification email sent. Check your inbox.';
            
            // Note: In a real application, you would send an actual email here
            // For this demonstration, we'll skip that step for simplicity
        } else {
            $response['message'] = 'Email not found';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Email verification failed: ' . $e->getMessage();
    }
}

// Handle password reset
else if (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $response['message'] = 'Please fill in all fields';
        echo json_encode($response);
        exit;
    }
    
    if ($newPassword !== $confirmPassword) {
        $response['message'] = 'Passwords do not match';
        echo json_encode($response);
        exit;
    }
    
    // Validate password strength
    if (strlen($newPassword) < 8 || 
        !preg_match('/[A-Z]/', $newPassword) || 
        !preg_match('/[0-9]/', $newPassword) || 
        !preg_match('/[!@#$%^&*]/', $newPassword)) {
        
        $response['message'] = 'Password does not meet the requirements';
        echo json_encode($response);
        exit;
    }
    
    // Check if we have an email in session
    if (!isset($_SESSION['reset_email'])) {
        $response['message'] = 'Reset session expired. Please try again.';
        echo json_encode($response);
        exit;
    }
    
    $email = $_SESSION['reset_email'];
    
    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);
        
        if ($stmt->rowCount() > 0) {
            // Clear reset session data
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_code']);
            unset($_SESSION['reset_time']);
            
            $response['success'] = true;
            $response['message'] = 'Password reset successful!';
        } else {
            $response['message'] = 'Password reset failed';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Password reset failed: ' . $e->getMessage();
    }
}

// Return response as JSON
echo json_encode($response);
?>