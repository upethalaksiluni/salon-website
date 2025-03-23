<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$pageTitle = "Admin Page";
$activePage = "default";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sulochana Salon</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <?php if (isset($additionalStyles)) echo $additionalStyles; ?>
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
        <div class="main-content">
            <div class="content-box">
                <!-- Page specific content goes here -->
                <?php if (isset($pageContent)) echo $pageContent; ?>
            </div>
        </div>
    </div>
    
    <footer class="admin-footer">
        <?php include 'admin_footer.dat'; ?>
    </footer>

    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <?php if (isset($additionalScripts)) echo $additionalScripts; ?>
</body>
</html>