<?php
session_start();
include "db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$activePage = 'admins';

try {
    // Get all admins
    $stmt = $conn->prepare("
        SELECT a.*, 
               COALESCE(al.action_count, 0) as action_count,
               al.last_action
        FROM admin a
        LEFT JOIN (
            SELECT admin_id, 
                   COUNT(*) as action_count,
                   MAX(created_at) as last_action
            FROM admin_audit_log 
            GROUP BY admin_id
        ) al ON a.id = al.admin_id
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle admin creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create') {
        try {
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $fullname = $_POST['fullname'];
            $email = $_POST['email'];

            $stmt = $conn->prepare("
                INSERT INTO admin (username, password, fullname, email) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$username, $password, $fullname, $email]);
            
            $_SESSION['success_message'] = "Admin created successfully!";
            header('Location: manage_admins.php');
            exit;
        } catch (PDOException $e) {
            $error = "Error creating admin: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Administrators - Sulochana Salon</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_header.css">
    <link rel="stylesheet" href="assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="assets/css/admin_footer.css">
    <link rel="stylesheet" href="assets/css/manage_admins.css">
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>
    
    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
        <div class="main-content">
            <div class="container mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title">Manage Administrators</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                        <i class="fas fa-plus"></i> Add New Admin
                    </button>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card admin-list-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover admin-table">
                                <thead>
                                    <tr>
                                        <th>Admin Info</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Last Login</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="admin-avatar">
                                                        <?php if (!empty($admin['profile_image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($admin['profile_image']); ?>" 
                                                                alt="Profile" class="profile-image">
                                                        <?php else: ?>
                                                            <i class="fas fa-user-circle"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="admin-info">
                                                        <div class="admin-name">
                                                            <?php echo htmlspecialchars($admin['fullname']); ?>
                                                        </div>
                                                        <div class="admin-role">Administrator</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                            <td>
                                                <?php 
                                                    echo $admin['last_login'] 
                                                        ? date('M d, Y H:i', strtotime($admin['last_login']))
                                                        : 'Never';
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $admin['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($admin['status'] ?? 'active'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-admin"
                                                            data-id="<?php echo $admin['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-admin"
                                                                data-id="<?php echo $admin['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($admin['fullname']); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
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

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addAdminForm" method="POST" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <?php include 'admin_footer.dat'; ?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/manage_admins.js"></script>
</body>
</html>