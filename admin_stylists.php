<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            s.*,
            COUNT(DISTINCT a.id) as total_appointments,
            AVG(f.rating) as avg_rating,
            (
                SELECT COUNT(*) 
                FROM appointment 
                WHERE stylist_id = s.id 
                AND appointment_date = CURRENT_DATE
            ) as today_appointments
        FROM stylists s
        LEFT JOIN appointment a ON s.id = a.stylist_id
        LEFT JOIN feedback f ON a.id = f.appointment_id
        GROUP BY s.id
        ORDER BY s.status ASC, s.name ASC
    ");
    $stmt->execute();
    $stylists = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stylists - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link href="assets/css/admin_header.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin_stylists.css">
</head>
<body class="admin-body">
    <?php 
    $pageTitle = "Manage Stylists";
    $activePage = "stylists";
    $pageStyles = [
        'assets/css/admin_stylists.css'
    ];
    $pageScripts = [
        'assets/js/admin_stylists.js'
    ];
    include 'admin_header.dat'; 
    ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
        <div class="admin-wrapper">
        <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Stylists</h1>
            <div>
                <a href="add_stylist.php" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> Add New Stylist
                </a>
                <a href="admindashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
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

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stylists Grid -->
        <div class="row">
            <?php foreach ($stylists as $stylist): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($stylist['name']); ?></h5>
                            <span class="badge bg-<?php echo $stylist['status'] === 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($stylist['status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <i class="fas fa-phone-alt"></i> 
                                <?php echo htmlspecialchars($stylist['phone']); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-envelope"></i> 
                                <?php echo htmlspecialchars($stylist['email']); ?>
                            </p>
                            <?php if ($stylist['specialization']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-star"></i> 
                                    <?php echo htmlspecialchars($stylist['specialization']); ?>
                                </p>
                            <?php endif; ?>
                            <div class="row text-center mt-3">
                                <div class="col">
                                    <h6>Today's Appointments</h6>
                                    <p class="mb-0"><?php echo $stylist['today_appointments']; ?></p>
                                </div>
                                <div class="col">
                                    <h6>Total Appointments</h6>
                                    <p class="mb-0"><?php echo $stylist['total_appointments']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="edit_stylist.php?id=<?php echo $stylist['id']; ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="process_stylist.php?action=toggle_status&id=<?php echo $stylist['id']; ?>&status=<?php echo $stylist['status'] === 'active' ? 'inactive' : 'active'; ?>" 
                               class="btn btn-sm btn-outline-<?php echo $stylist['status'] === 'active' ? 'danger' : 'success'; ?>"
                               onclick="return confirm('Are you sure you want to <?php echo $stylist['status'] === 'active' ? 'deactivate' : 'activate'; ?> this stylist?')">
                                <?php echo $stylist['status'] === 'active' ? 
                                    '<i class="fas fa-ban"></i> Deactivate' : 
                                    '<i class="fas fa-check"></i> Activate'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        </div>
        </div>
        </div>

    <?php include 'admin_footer.dat'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/admin_stylists.js"></script>
</body>
</html>