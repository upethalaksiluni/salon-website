<?php
session_start();
$pageTitle = "Book Services";
$activePage = "book";
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user profile data for header
if (!isset($userProfile)) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
}

try {
    $stmt = $conn->prepare("
        SELECT 
            id, name, description, price, duration, category, image_url
        FROM services 
        WHERE status = 'active'
        ORDER BY FIELD(category, 
            'Hair Services', 
            'Facial & Skin Care Services', 
            'Nail Care Services', 
            'Makeup Services'
        ), name
    ");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group services by category
    $servicesByCategory = [];
    foreach ($services as $service) {
        $servicesByCategory[$service['category']][] = $service;
    }
} catch (PDOException $e) {
    $errorMsg = "Error fetching services: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sulochana Salon</title>
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/client-sidebar.css">
    <link rel="stylesheet" href="assets/css/client-footer.css">
    <style>
        .service-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .container {
            margin-bottom: 30px;
            margin-top: 130px;
        }

        .service-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .service-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
        }

        .summary-card {
            position: sticky;
            bottom: 20px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(74,0,224,0.1);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="client-wrapper">
        <?php include 'client_sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'client_header.dat'; ?>
            
            <div class="container py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">Our Services</h1>
                        <p class="text-muted">Choose from our wide range of professional beauty services</p>
                    </div>
                </div>

                <!-- Services Form -->
                <form action="appointment.php" method="POST" id="serviceForm">
                    <?php if (!empty($servicesByCategory)): ?>
                        <?php foreach ($servicesByCategory as $category => $categoryServices): ?>
                            <!-- Service Categories -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h3 class="h5 mb-0"><?php echo htmlspecialchars($category); ?></h3>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <?php foreach ($categoryServices as $service): ?>
                                            <!-- Individual Service Cards -->
                                            <div class="col-md-6 col-lg-4">
                                                <div class="service-card">
                                                    <div class="form-check">
                                                        <input class="form-check-input service-checkbox" 
                                                               type="checkbox" 
                                                               name="services[]" 
                                                               value="<?php echo $service['id']; ?>"
                                                               data-price="<?php echo $service['price']; ?>"
                                                               data-duration="<?php echo $service['duration']; ?>"
                                                               id="service<?php echo $service['id']; ?>">
                                                        <label class="form-check-label w-100" for="service<?php echo $service['id']; ?>">
                                                            <?php if ($service['image_url']): ?>
                                                                <img src="<?php echo htmlspecialchars($service['image_url']); ?>" 
                                                                     alt="<?php echo htmlspecialchars($service['name']); ?>"
                                                                     class="service-image mb-3">
                                                            <?php endif; ?>
                                                            <h4 class="h6 mb-2"><?php echo htmlspecialchars($service['name']); ?></h4>
                                                            <p class="small text-muted mb-2"><?php echo htmlspecialchars($service['description']); ?></p>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="text-primary fw-bold">$<?php echo number_format($service['price'], 2); ?></span>
                                                                <span class="text-muted small"><?php echo $service['duration']; ?> mins</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Summary Card -->
                        <div class="card mb-4 summary-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-3">Selected Services Summary</h5>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Duration:</span>
                                            <strong id="totalDuration">0 mins</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Total Amount:</span>
                                            <strong id="totalAmount">$0.00</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary btn-lg w-100" id="continueBtn" disabled>
                                            Continue to Booking
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No services available at the moment.</div>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php include 'client_footer.dat'; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/client-sidebar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.service-checkbox');
            const totalDurationElement = document.getElementById('totalDuration');
            const totalAmountElement = document.getElementById('totalAmount');
            const continueBtn = document.getElementById('continueBtn');

            function updateTotals() {
                let totalDuration = 0;
                let totalAmount = 0;
                let servicesSelected = false;

                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        servicesSelected = true;
                        totalDuration += parseInt(checkbox.dataset.duration);
                        totalAmount += parseFloat(checkbox.dataset.price);
                    }
                });

                totalDurationElement.textContent = `${totalDuration} mins`;
                totalAmountElement.textContent = `$${totalAmount.toFixed(2)}`;
                continueBtn.disabled = !servicesSelected;
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateTotals);
            });

            // Add animation to service cards on hover
            const serviceCards = document.querySelectorAll('.service-card');
            serviceCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>