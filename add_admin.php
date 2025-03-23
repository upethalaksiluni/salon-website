<?php
session_start();
include "db_connect.php";

// Check if admin is logged in and is super admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    header('Location: adminlogin.php');
    exit;
}

$activePage = 'admins';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already exists");
        }

        // Insert new admin
        $stmt = $conn->prepare("
            INSERT INTO admin (username, password, fullname, email, phone, status, role)
            VALUES (?, ?, ?, ?, ?, 'active', 'admin')
        ");

        $stmt->execute([
            $_POST['username'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['fullname'],
            $_POST['email'],
            $_POST['phone'] ?? null
        ]);

        $_SESSION['success_message'] = "Admin created successfully";
        header('Location: manage_admins.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Administrator - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/admin_header.css">
    <link rel="stylesheet" href="assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="assets/css/admin_footer.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>
    
    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
        <div class="main-content">
            <div class="container mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Add New Administrator</h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                        <div class="invalid-feedback">Please enter a username</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback">Please enter a password</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="fullname" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" required>
                                        <div class="invalid-feedback">Please enter the full name</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">Please enter a valid email</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone (Optional)</label>
                                        <input type="tel" class="form-control" id="phone" name="phone">
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-primary">Create Admin</button>
                                        <a href="manage_admins.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.dat'; ?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script>
        // Password visibility toggle
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const input = document.getElementById('password');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    </script>
</body>
</html>