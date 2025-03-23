<?php
// service.php
// Define the path to the header.dat file
$headerFile = "header.dat"; // Adjust the path to your .dat file if needed
$headerContent = '';

// Check if the file exists and is readable
if (file_exists($headerFile) && is_readable($headerFile)) {
    // Read the content of the header.dat file
    $headerContent = file_get_contents($headerFile);
} else {
    $headerContent = 'Error loading header content from header.dat.';
}
?>

<?php
// service.php
// Define the path to the service.dat file
$serviceFile = "service.dat"; // Adjust the path to your .dat file if needed
$serviceContent = '';

// Check if the file exists and is readable
if (file_exists($serviceFile) && is_readable($serviceFile)) {
    // Read the content of the service.dat file
    $serviceContent = file_get_contents($serviceFile);
} else {
    $serviceContent = 'Error loading service content from service.dat.';
}
?>

<?php
// service.php
// Define the path to the footer.dat file
$footerFile = "footer.dat"; // Adjust the path to your .dat file if needed
$footerContent = '';

// Check if the file exists and is readable
if (file_exists($footerFile) && is_readable($footerFile)) {
    // Read the content of the footer.dat file
    $footerContent = file_get_contents($footerFile);
} else {
    $footerContent = 'Error loading index foot content from footer.dat.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Sulochana Salon</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="56x56" href="assets/images/fav-icon/icon.png">
    <!-- bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css" media="all">
    <!-- carousel CSS -->
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css" type="text/css" media="all">
    <!-- animate CSS -->
    <link rel="stylesheet" href="assets/css/animate.css" type="text/css" media="all">
    <!-- animated-text CSS -->
    <link rel="stylesheet" href="assets/css/animated-text.css" type="text/css" media="all">
    <!-- font-awesome CSS -->
    <link rel="stylesheet" href="assets/css/all.min.css" type="text/css" media="all">
    <!-- theme-default CSS -->
    <link rel="stylesheet" href="assets/css/theme-default.css" type="text/css" media="all">
    <!-- meanmenu CSS -->
    <link rel="stylesheet" href="assets/css/meanmenu.min.css" type="text/css" media="all">
    <!-- transitions CSS -->
    <link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css" media="all">
    <!-- venobox CSS -->
    <link rel="stylesheet" href="venobox/venobox.css" type="text/css" media="all">
    <!-- bootstrap icons -->
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css" type="text/css" media="all">
    <!-- Main Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/service.css" type="text/css" media="all">
    <!-- responsive CSS -->
    <link rel="stylesheet" href="assets/css/responsive.css" type="text/css" media="all">
    <!-- Coustom Animation CSS -->
    <link rel="stylesheet" href="assets/css/coustom-animation.css" type="text/css" media="all">
    <link rel="stylesheet" href="commservicecss.dat">

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- modernizr js -->
    <script src="assets/js/vendor/modernizr-3.5.0.min.js"></script>


        <?php
            $cssFiles = ["headercss.dat", "servicecss.dat", "footercss.dat"];
            foreach ($cssFiles as $file) {
                if (file_exists($file) && is_readable($file)) {
                    echo "<style>" . file_get_contents($file) . "</style>";
                }
            }
        ?>
</head>
<body>
    <div class="content">
            <!-- Content loaded from the header.dat file -->
            <?php echo $headerContent; ?>
        </div>

        <div class="content">
            <!-- Content loaded from the service.dat file -->
            <?php echo $serviceContent; ?>
        </div>

        <div class="content">
            <!-- Content loaded from the index.dat file -->
            <?php echo $footerContent; ?>
        </div>

    <?php
      // Load header JavaScript from headerjs.dat
      $jsFiles = ["headerjs.dat","servicejs.dat"];
      foreach ($jsFiles as $file) {
        if (file_exists($file) && is_readable($file)) {
          echo "<script>" . file_get_contents($file) . "</script>";
        } else {
          echo "<!-- Error: File $file not found or not readable -->";
        }
      }
    ?>

    <!-- loder -->
    <div class="loader-wrapper">
        <span class="loader"></span>
        <div class="loder-section left-section"></div>
        <div class="loder-section right-section"></div>
    </div>
 
    <!--==================================================-->
    <!-- Start Toptech Scroll Up-->
    <!--==================================================-->
    <div class="prgoress_indicator active-progress">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" style="transition: stroke-dashoffset 10ms linear 0s; stroke-dasharray: 307.919, 307.919; stroke-dashoffset: 212.78;"></path>
        </svg>
    </div>
    <!--==================================================-->
    <!-- End Toptech Scroll Up-->
    <!--==================================================-->

    <!-- jquery js -->
    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <!-- bootstrap js -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- carousel js -->
    <script src="assets/js/owl.carousel.min.js"></script>
    <!-- animated-text js -->
    <script src="assets/js/animated-text.js"></script>
    <!-- wow js -->
    <script src="assets/js/wow.js"></script>
    <!-- ajax-mail js -->
    <script src="assets/js/ajax-mail.js"></script>
    <!-- imagesloaded js -->
    <script src="assets/js/imagesloaded.pkgd.min.js"></script>
    <!-- venobox js -->
    <script src="venobox/venobox.js"></script>
    <!--  animated-text js -->
    <script src="assets/js/animated-text.js"></script>
    <!-- venobox min js -->
    <script src="venobox/venobox.min.js"></script>
    <!-- jquery meanmenu js -->
    <script src="assets/js/jquery.meanmenu.js"></script>
    <script src="assets/js/service.js"></script>
    <!-- theme js -->
    <script src="assets/js/theme.js"></script>
    <!-- Cousom carousel js -->
    <script src="assets/js/coustom-carousel.js"></script>
    <script src="assets/js/scroll-up.js"></script>
</body>
</html>