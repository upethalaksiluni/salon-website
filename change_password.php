<?php
session_start();
include "db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$isAdmin = isset($_SESSION['admin_id']);
$userId = $isAdmin ? $_SESSION['admin_id'] : $_SESSION['user_id'];
$userTable = $isAdmin ? 'admin' : 'user';
$activePage = 'profile';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    try {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM $userTable WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (password_verify($currentPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE $userTable SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);
                
                $_SESSION['success_message'] = "Password changed successfully!";
                header('Location: ' . ($isAdmin ? 'admin_dashboard.php' : 'client_dashboard.php'));
                exit;
            } else {
                $error = "New passwords do not match!";
            }
        } else {
            $error = "Current password is incorrect!";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Sulochana Salon</title>
    <?php if ($isAdmin): ?>
        <link rel="stylesheet" href="assets/css/admin_header.css">
        <link rel="stylesheet" href="assets/css/admin_sidebar.css">
        <link rel="stylesheet" href="assets/css/admin_footer.css">
    <?php else: ?>
        <link rel="stylesheet" href="assets/css/client_header.css">
        <link rel="stylesheet" href="assets/css/client-sidebar.css">
        <link rel="stylesheet" href="assets/css/client-footer.css">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/change_password.css">
</head>
<body class="<?php echo $isAdmin ? 'admin-body' : ''; ?>">
    <?php 
    if ($isAdmin) {
        include 'admin_header.dat';
        include 'admin_sidebar.dat';
    } else {
        include 'client_header.dat';
        include 'client_sidebar.php';
    }
    ?>

    <div class="main-content">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Change Password</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="POST" class="password-form">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="password-input-group">
                                        <input type="password" class="form-control" id="current_password" 
                                               name="current_password" required>
                                        <i class="fas fa-eye toggle-password"></i>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="password-input-group">
                                        <input type="password" class="form-control" id="new_password" 
                                               name="new_password" required>
                                        <i class="fas fa-eye toggle-password"></i>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="password-input-group">
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" required>
                                        <i class="fas fa-eye toggle-password"></i>
                                    </div>
                                </div>

                                <div class="password-requirements mb-3">
                                    <h6>Password Requirements:</h6>
                                    <ul>
                                        <li id="length">At least 8 characters long</li>
                                        <li id="uppercase">Contains uppercase letter</li>
                                        <li id="lowercase">Contains lowercase letter</li>
                                        <li id="number">Contains number</li>
                                        <li id="special">Contains special character</li>
                                    </ul>
                                </div>

                                <button type="submit" class="btn btn-primary">Change Password</button>
                                <a href="<?php echo $isAdmin ? 'admindashboard.php' : 'client_dashboard.php'; ?>" 
                                   class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php 
    if ($isAdmin) {
        include 'admin_footer.dat';
    } else {
        include 'client_footer.dat';
    }
    ?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/change_password.js"></script>
</body>
</html>