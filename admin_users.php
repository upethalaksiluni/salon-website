<?php
session_start();
include "db_connect.php";

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

try {
    // Get all users with their last appointment
    $stmt = $conn->prepare("
        SELECT 
            u.*,
            COUNT(DISTINCT a.id) as total_appointments,
            MAX(a.appointment_date) as last_visit,
            AVG(f.rating) as avg_rating
        FROM user u
        LEFT JOIN appointment a ON u.id = a.user_id
        LEFT JOIN feedback f ON a.id = f.appointment_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMsg = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link href="assets/css/admin_header.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin_footer.css">
    <link rel="stylesheet" href="assets/css/admin_users.css">
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>
    
    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
        <div class="main-content">
            <div class="container mt-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Manage Users</h1>
                    <a href="admindashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Appointments</th>
                                        <th>Last Visit</th>
                                        <th>Rating</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($user['fullname']); ?>
                                                <br>
                                                <small class="text-muted">
                                                    @<?php echo htmlspecialchars($user['username']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($user['phone']); ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($user['email']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo $user['total_appointments']; ?></td>
                                            <td>
                                                <?php 
                                                echo $user['last_visit'] 
                                                    ? date('M d, Y', strtotime($user['last_visit']))
                                                    : 'Never';
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($user['avg_rating']) {
                                                    $rating = round($user['avg_rating'], 1);
                                                    echo str_repeat('⭐', $rating) . ' ' . $rating;
                                                } else {
                                                    echo 'No ratings';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info view-details" data-id="<?php echo $user['id']; ?>" 
                                                            title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary view-appointments" 
                                                            data-id="<?php echo $user['id']; ?>" title="View Appointments">
                                                        <i class="fas fa-calendar"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning view-feedback" 
                                                            data-id="<?php echo $user['id']; ?>" title="View Feedback">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-user" 
                                                            data-id="<?php echo $user['id']; ?>" title="Delete User">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-footer">
        <?php include 'admin_footer.dat'; ?>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="userDetailsContent">Loading...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/admin_users.js"></script>
    <script>
        // User details modal functionality
        document.querySelectorAll('.view-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
                
                // Fetch user details
                fetch(`get_user_details.php?id=${userId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('userDetailsContent').innerHTML = data;
                        modal.show();
                    });
            });
        });
    </script>
</body>
</html>