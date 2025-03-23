<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$activePage = 'services';

try {
    // Get all services grouped by category
    $stmt = $conn->prepare("
        SELECT category, GROUP_CONCAT(
            JSON_OBJECT(
                'id', id,
                'name', name,
                'description', description,
                'duration', duration,
                'price', price,
                'image_url', image_url
            )
        ) as services
        FROM services 
        WHERE status = 'active' 
        GROUP BY category
    ");
    $stmt->execute();
    $serviceCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/client-sidebar.css">
    <link rel="stylesheet" href="assets/css/client_header.css">
    <link rel="stylesheet" href="assets/css/client-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            margin-top: var(--header-height);
            transition: margin-left 0.3s ease;
        }

        .services-header {
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .service-category {
            margin-bottom: 2rem;
        }

        .category-header {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .category-header:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .category-header h3 {
            margin: 0;
            color: #4a00e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .service-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .service-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .service-image {
            width: 120px;
            height: 120px;
            border-radius: 10px;
            object-fit: cover;
        }

        .service-info {
            flex: 1;
        }

        .service-price {
            font-size: 1.25rem;
            color: #4a00e0;
            font-weight: bold;
        }

        .service-duration {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .category-icon {
            width: 40px;
            height: 40px;
            background: #4a00e0;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .collapse-icon {
            transition: transform 0.3s ease;
        }

        .collapsed .collapse-icon {
            transform: rotate(-180deg);
        }
    </style>
</head>
<body>
    <?php include 'client_header.dat'; ?>
    <?php include 'client_sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="services-header">
                <h1><i class="fas fa-spa"></i> Our Services</h1>
                <p class="mb-0">Explore our wide range of professional beauty and wellness services</p>
            </div>

            <?php foreach ($serviceCategories as $category): ?>
                <div class="service-category">
                    <div class="category-header" data-bs-toggle="collapse" 
                         data-bs-target="#category<?php echo md5($category['category']); ?>">
                        <h3>
                            <span>
                                <div class="category-icon">
                                    <?php
                                    $icon = 'fa-spa'; // default icon
                                    switch(strtolower($category['category'])) {
                                        case 'hair services':
                                            $icon = 'fa-cut';
                                            break;
                                        case 'facial & skin care':
                                            $icon = 'fa-face-smile';
                                            break;
                                        case 'nail care':
                                            $icon = 'fa-hand-sparkles';
                                            break;
                                        case 'makeup':
                                            $icon = 'fa-magic';
                                            break;
                                    }
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <?php echo htmlspecialchars($category['category']); ?>
                            </span>
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </h3>
                    </div>
                    
                    <div class="collapse show" id="category<?php echo md5($category['category']); ?>">
                        <?php 
                        $services = json_decode('[' . $category['services'] . ']', true);
                        foreach ($services as $service): 
                        ?>
                            <div class="service-card">
                                <img src="<?php echo htmlspecialchars($service['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($service['name']); ?>"
                                     class="service-image">
                                <div class="service-info">
                                    <h4><?php echo htmlspecialchars($service['name']); ?></h4>
                                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="service-price">
                                            Rs. <?php echo number_format($service['price'], 2); ?>
                                        </span>
                                        <span class="service-duration">
                                            <i class="far fa-clock"></i> 
                                            <?php echo htmlspecialchars($service['duration']); ?> minutes
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'client_footer.dat'; ?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/client-sidebar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add collapsed class to header when collapse is hidden
            const collapseElements = document.querySelectorAll('.collapse');
            collapseElements.forEach(collapse => {
                collapse.addEventListener('hide.bs.collapse', function() {
                    this.previousElementSibling.classList.add('collapsed');
                });
                collapse.addEventListener('show.bs.collapse', function() {
                    this.previousElementSibling.classList.remove('collapsed');
                });
            });
        });
    </script>
</body>
</html>