<?php
session_start();
include "db_connect.php";

// Check if admin is logged in and is super admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    header('Location: adminlogin.php');
    exit;
}

$activePage = 'admins';

// Get admin details
if (!isset($_GET['id'])) {
    header('Location: manage_admins.php');
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $admin = $stmt->fetch();

    if (!$admin) {
        throw new Exception("Admin not found");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate input
        if (empty($_POST['fullname']) || empty($_POST['email'])) {
            throw new Exception("Required fields cannot be empty");
        }

        // Check if email exists for other admins
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
        $stmt->execute([$_POST['email'], $admin['id']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already exists");
        }

        // Update admin
        $stmt = $conn->prepare("
            UPDATE admin 
            SET fullname = ?, email = ?, phone = ?, status = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['fullname'],
            $_POST['email'],
            $_POST['phone'] ?? null,
            $_POST['status'],
            $admin['id']
        ]);

        $_SESSION['success_message'] = "Admin updated successfully";
        header('Location: manage_admins.php');
        exit;
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Administrator - Sulochana Salon</title>
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
                                <h5 class="card-title mb-0">Edit Administrator</h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                                        <small class="text-muted">Username cannot be changed</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="fullname" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" 
                                               value="<?php echo htmlspecialchars($admin['fullname']); ?>" required>
                                        <div class="invalid-feedback">Please enter the full name</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                        <div class="invalid-feedback">Please enter a valid email</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone (Optional)</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($admin['phone']); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="active" <?php echo $admin['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $admin['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-primary">Update Admin</button>
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