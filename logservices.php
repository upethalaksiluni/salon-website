<?php
// Start session
session_start();

// Include database connection
include "db_connect.php";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Fetch services from database
try {
    $stmt = $conn->prepare("SELECT * FROM services ORDER BY category, name");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group services by category
    $servicesByCategory = [];
    foreach ($services as $service) {
        if (!isset($servicesByCategory[$service['category']])) {
            $servicesByCategory[$service['category']] = [];
        }
        $servicesByCategory[$service['category']][] = $service;
    }
} catch (PDOException $e) {
    $error = "Error fetching services: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container mt-5">
        <h1 class="text-center mb-4">Our Services</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form id="serviceForm" action="appointment.php" method="post">
            <?php if (empty($servicesByCategory)): ?>
                <div class="alert alert-info">No services available at the moment.</div>
            <?php else: ?>
                <?php foreach ($servicesByCategory as $category => $categoryServices): ?>
                    <div class="service-category mb-4">
                        <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
                        <div class="service-list">
                            <?php foreach ($categoryServices as $service): ?>
                                <div class="service-item">
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" 
                                               name="selected_services[]" 
                                               value="<?php echo $service['id']; ?>" 
                                               id="service-<?php echo $service['id']; ?>"
                                               data-price="<?php echo $service['price']; ?>">
                                        <label class="form-check-label" for="service-<?php echo $service['id']; ?>">
                                            <span class="service-name"><?php echo htmlspecialchars($service['name']); ?></span>
                                            <span class="service-price">$<?php echo number_format($service['price'], 2); ?></span>
                                            <?php if (!empty($service['description'])): ?>
                                                <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="summary-section mt-4 mb-4">
                    <h3>Selected Services</h3>
                    <div id="selectedServices" class="selected-services">
                        <p class="text-muted">No services selected</p>
                    </div>
                    <div class="total-amount">
                        <strong>Total:</strong> $<span id="totalAmount">0.00</span>
                    </div>
                </div>
                
                <?php if ($isLoggedIn): ?>
                    <button type="submit" class="btn btn-primary btn-lg">Book Appointment</button>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Please <a href="login.php">login</a> to book an appointment.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
            const selectedServicesDiv = document.getElementById('selectedServices');
            const totalAmountSpan = document.getElementById('totalAmount');
            
            function updateSelectedServices() {
                let selectedServices = [];
                let totalAmount = 0;
                
                serviceCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const serviceId = checkbox.value;
                        const serviceName = checkbox.nextElementSibling.querySelector('.service-name').textContent;
                        const servicePrice = parseFloat(checkbox.dataset.price);
                        
                        selectedServices.push({ id: serviceId, name: serviceName, price: servicePrice });
                        totalAmount += servicePrice;
                    }
                });
                
                // Update the selected services display
                if (selectedServices.length === 0) {
                    selectedServicesDiv.innerHTML = '<p class="text-muted">No services selected</p>';
                } else {
                    let html = '<ul class="list-group">';
                    selectedServices.forEach(service => {
                        html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${service.name}
                                    <span class="badge bg-primary rounded-pill">$${service.price.toFixed(2)}</span>
                                </li>`;
                    });
                    html += '</ul>';
                    selectedServicesDiv.innerHTML = html;
                }
                
                // Update the total amount
                totalAmountSpan.textContent = totalAmount.toFixed(2);
            }
            
            // Add event listeners to checkboxes
            serviceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedServices);
            });
        });
    </script>
</body>
</html>