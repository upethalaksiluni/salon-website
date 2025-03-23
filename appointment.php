<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || empty($_POST['services'])) {
    header('Location: book_services.php');
    exit;
}

try {
    // Fetch selected services details
    $serviceIds = $_POST['services'];
    $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
    
    $stmt = $conn->prepare("
        SELECT id, name, price, duration
        FROM services 
        WHERE id IN ($placeholders) AND status = 'active'
    ");
    $stmt->execute($serviceIds);
    $selectedServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totalDuration = array_sum(array_column($selectedServices, 'duration'));
    $totalAmount = array_sum(array_column($selectedServices, 'price'));
    
    // Get available stylists
    $stmt = $conn->prepare("SELECT id, name FROM stylists WHERE status = 'active' ORDER BY name");
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
    <title>Book Appointment - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/client_header.css">
    <link rel="stylesheet" href="assets/css/client-sidebar.css">
    <link rel="stylesheet" href="assets/css/client-footer.css">
    <link rel="stylesheet" href="assets/css/appointment.css">

    <!-- <style>
        .admin-body {
            background-color: #f9f9f9;
        } -->
    <!-- </style> -->
</head>
<body class="admin-body">
    <?php include 'client_header.dat'; ?>

    <div class="admin-wrapper">
        <?php include 'client_sidebar.php'; ?>
<div class="container mt-5">
        <h1>Book Appointment</h1>
        
        <?php if (isset($errorMsg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>
        
        <form action="process_appointment.php" method="POST">
            <!-- Selected Services Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Selected Services</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($selectedServices as $service): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo htmlspecialchars($service['name']); ?></span>
                            <span>$<?php echo number_format($service['price'], 2); ?></span>
                        </div>
                        <input type="hidden" name="selected_services[]" value="<?php echo $service['id']; ?>">
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total Duration:</strong>
                        <strong><?php echo $totalDuration; ?> mins</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong>Total Amount:</strong>
                        <strong>$<?php echo number_format($totalAmount, 2); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Appointment Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Appointment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="appointmentDate" class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" id="appointmentDate" name="appointment_date" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="appointmentTime" class="form-label">Preferred Time</label>
                            <select class="form-select" id="appointmentTime" name="appointment_time" required>
                                <option value="">Select Time</option>
                                <?php
                                $start = strtotime('09:00');
                                $end = strtotime('17:00');
                                for ($time = $start; $time <= $end; $time += 1800) {
                                    echo '<option value="' . date('H:i', $time) . '">' . 
                                         date('h:i A', $time) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stylist" class="form-label">Preferred Stylist (Optional)</label>
                        <select class="form-select" id="stylist" name="stylist_id">
                            <option value="">No Preference</option>
                            <?php foreach ($stylists as $stylist): ?>
                                <option value="<?php echo $stylist['id']; ?>">
                                    <?php echo htmlspecialchars($stylist['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="special_instructions" class="form-label">Special Instructions (Optional)</label>
                        <textarea class="form-control" id="special_instructions" name="special_instructions" 
                                rows="3" maxlength="500"></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Book Appointment</button>
            <a href="book_services.php" class="btn btn-outline-secondary">Back to Services</a>
        </form>
    </div>

    <?php include 'client_footer.dat'; ?>

    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/client-sidebar.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('appointmentDate');
            const timeSelect = document.getElementById('appointmentTime');
            
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
            
            // Add date change handler to check availability
            dateInput.addEventListener('change', function() {
                checkAvailability();
            });
            
            timeSelect.addEventListener('change', function() {
                checkAvailability();
            });
            
            function checkAvailability() {
                const date = dateInput.value;
                const time = timeSelect.value;
                
                if (date && time) {
                    fetch(`check_availability.php?date=${date}&time=${time}`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.available) {
                                alert('This time slot is not available. Please select another time.');
                                timeSelect.value = '';
                            }
                        });
                }
            }
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            // Submit form
            this.submit();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all dropdowns
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl)
        });
    });
    </script>
</body>
</html>

