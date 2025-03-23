<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // Get available stylists
    $stmt = $conn->prepare("
        SELECT id, name, specialization 
        FROM stylists 
        WHERE status = 'active'
        ORDER BY name
    ");
    $stmt->execute();
    $stylists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get user's information
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <?php include 'admin_header.dat'; ?>

    <div class="admin-wrapper">
        <?php include 'admin_sidebar.dat'; ?>
        
    <div class="container mt-5">
        <h1>Book Your Appointment</h1>
        
        <?php if (isset($errorMsg)): ?>
            <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <form action="process_appointment.php" method="POST" class="needs-validation" novalidate>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Choose Date & Time</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="appointmentDate" class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" id="appointmentDate" 
                                   name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="appointmentTime" class="form-label">Preferred Time</label>
                            <select class="form-select" id="appointmentTime" name="appointment_time" required>
                                <option value="">Select time...</option>
                                <?php
                                $start = strtotime('09:00');
                                $end = strtotime('18:00');
                                for ($time = $start; $time <= $end; $time += 1800) {
                                    echo '<option value="' . date('H:i:s', $time) . '">' 
                                         . date('h:i A', $time) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="stylist" class="form-label">Preferred Stylist (Optional)</label>
                        <select class="form-select" id="stylist" name="stylist_id">
                            <option value="">No preference</option>
                            <?php foreach ($stylists as $stylist): ?>
                                <option value="<?php echo $stylist['id']; ?>">
                                    <?php echo htmlspecialchars($stylist['name']); ?> 
                                    (<?php echo htmlspecialchars($stylist['specialization']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="special_instructions" class="form-label">
                            Special Instructions (Optional)
                        </label>
                        <textarea class="form-control" id="special_instructions" 
                                name="special_instructions" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Confirm Booking</button>
                <a href="book_services.php" class="btn btn-outline-secondary">Back to Services</a>
            </div>
        </form>
    </div>

    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const dateInput = document.getElementById('appointmentDate');
            const timeSelect = document.getElementById('appointmentTime');

            // Validate form before submission
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });

            // Check availability when date or time changes
            [dateInput, timeSelect].forEach(element => {
                element.addEventListener('change', checkAvailability);
            });

            function checkAvailability() {
                const date = dateInput.value;
                const time = timeSelect.value;
                
                if (date && time) {
                    fetch('check_availability.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            date: date,
                            time: time,
                            stylist_id: document.getElementById('stylist').value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.available) {
                            alert('This time slot is not available. Please choose another time.');
                            timeSelect.value = '';
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>