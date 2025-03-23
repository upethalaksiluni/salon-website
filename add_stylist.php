<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['name']) || empty($_POST['phone']) || empty($_POST['email'])) {
            throw new Exception("Please fill all required fields");
        }

        $stmt = $conn->prepare("
            INSERT INTO stylists (
                name, 
                specialization, 
                phone, 
                email, 
                status,
                created_at
            ) VALUES (?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->execute([
            trim($_POST['name']),
            trim($_POST['specialization']),
            trim($_POST['phone']),
            trim($_POST['email'])
        ]);

        $_SESSION['success_message'] = "Stylist added successfully";
        header('Location: admin_stylists.php');
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
    <title>Add New Stylist - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Add New Stylist</h3>
                        <a href="admin_stylists.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Name*</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Please enter stylist name</div>
                            </div>

                            <div class="mb-3">
                                <label for="specialization" class="form-label">Specialization</label>
                                <textarea class="form-control" id="specialization" name="specialization" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone*</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                                <div class="invalid-feedback">Please enter phone number</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email*</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter valid email</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Add Stylist</button>
                                <a href="admin_stylists.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
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