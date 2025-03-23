<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['appointment'])) {
    header('Location: dashboard.php');
    exit;
}

try {
    // Verify appointment belongs to user and is completed
    $stmt = $conn->prepare("
        SELECT a.*, s.name as service_name
        FROM appointment a
        LEFT JOIN appointment_services as_rel ON a.id = as_rel.appointment_id
        LEFT JOIN services s ON as_rel.service_id = s.id
        WHERE a.id = ? AND a.user_id = ? AND a.status = 'completed'
    ");
    $stmt->execute([$_GET['appointment'], $_SESSION['user_id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        throw new Exception("Invalid appointment or not completed yet.");
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/star-rating.css">
    <script src="assets/js/star-rating.js" defer></script>
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
        <h1>Service Feedback</h1>
        
        <form action="process_feedback.php" method="POST" id="feedbackForm">
            <input type="hidden" name="appointment_id" value="<?php echo $_GET['appointment']; ?>">
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Rate your experience</h5>
                    
                    <div class="mb-4">
                        <label class="form-label">Overall Rating</label>
                        <div class="star-rating">
                            <div class="stars">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" 
                                           name="rating" value="<?php echo $i; ?>" required>
                                    <label for="star<?php echo $i; ?>">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-text ms-2">Select rating</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service Quality</label>
                        <select class="form-select" name="service_quality" required>
                            <option value="">Select rating</option>
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="average">Average</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Comments</label>
                        <textarea class="form-control" name="comments" rows="4" 
                                placeholder="Share your experience with us..."></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Submit Feedback</button>
            <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </form>
    </div>

    <script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>