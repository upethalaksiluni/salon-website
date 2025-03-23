<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

try {
    // Get all services
    $stmt = $conn->prepare("
        SELECT 
            s.*, 
            COUNT(as_rel.appointment_id) as booking_count
        FROM services s
        LEFT JOIN appointment_services as_rel ON s.id = as_rel.service_id
        GROUP BY s.id
        ORDER BY s.category, s.name
    ");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get service categories
    $stmt = $conn->prepare("SELECT DISTINCT category FROM services ORDER BY category");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $errorMsg = "Error fetching services: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link href="assets/css/admin_header.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <?php 
    $pageTitle = "Manage Services";
    $pageStyles = [
        'assets/css/admin_services.css'
    ];
    $pageScripts = [
        'assets/js/admin_services.js'
    ];
    include 'admin_header.dat'; 
    ?>

<div class="admin-wrapper1">
<div class="admin-wrapper">
<?php include 'admin_sidebar.dat'; ?>
        
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Services</h1>
            <div>
                <a href="admindashboard.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="fas fa-plus"></i> Add New Service
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMsg)): ?>
            <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        

        <!-- Services Grid -->
        <div class="services-grid mt-4">
            <?php foreach ($services as $service): ?>
            <div class="service-card">
                <div class="service-card-header">
                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                    <span class="badge bg-<?php echo $service['status'] === 'active' ? 'success' : 'secondary'; ?>">
                        <?php echo ucfirst($service['status']); ?>
                    </span>
                </div>
                <div class="service-card-body">
                    <div class="service-details">
                        <p><i class="fas fa-tag"></i> <?php echo htmlspecialchars($service['category']); ?></p>
                        <p><i class="fas fa-clock"></i> <?php echo $service['duration']; ?> mins</p>
                        <p><i class="fas fa-dollar-sign"></i> <?php echo number_format($service['price'], 2); ?></p>
                        <p><i class="fas fa-calendar-check"></i> <?php echo $service['booking_count']; ?> bookings</p>
                    </div>
                </div>
                <div class="service-card-footer">
                    <button class="btn btn-sm btn-outline-primary edit-service" 
                            data-bs-toggle="modal"
                            data-bs-target="#editServiceModal"
                            data-id="<?php echo $service['id']; ?>"
                            data-name="<?php echo htmlspecialchars($service['name']); ?>"
                            data-category="<?php echo htmlspecialchars($service['category']); ?>"
                            data-price="<?php echo $service['price']; ?>"
                            data-duration="<?php echo $service['duration']; ?>"
                            data-description="<?php echo htmlspecialchars($service['description']); ?>"
                            data-status="<?php echo $service['status']; ?>">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <?php if ($service['booking_count'] == 0): ?>
                    <button class="btn btn-sm btn-outline-danger delete-service"
                            data-id="<?php echo $service['id']; ?>"
                            data-name="<?php echo htmlspecialchars($service['name']); ?>">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="process_service.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Service form fields -->
                        <div class="mb-3">
                            <label class="form-label">Service Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" name="category" 
                                   list="categories" required>
                            <datalist id="categories">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" name="duration" 
                                   min="15" step="15" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price ($)</label>
                            <input type="number" class="form-control" name="price" 
                                   min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="process_service.php" method="POST" id="editServiceForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="service_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Service Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" name="category" list="categories" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" name="duration" min="15" step="15" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price ($)</label>
                            <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
        </div>

    <?php include 'admin_footer.dat'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/admin.js"></script>
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin_services.js"></script>
</body>
</html>