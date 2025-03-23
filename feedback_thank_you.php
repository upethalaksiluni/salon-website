<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Sulochana Salon</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/thank-you.css">
</head>
<body>
    <div class="thank-you-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="thank-you-card">
                        <div class="success-animation">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                        </div>
                        
                        <h1 class="thank-you-title">Thank You for Your Feedback!</h1>
                        <p class="lead thank-you-message">We appreciate you taking the time to share your experience.</p>
                        <p class="feedback-note">Your feedback helps us improve our services.</p>
                        
                        <div class="come-again-section">
                            <h3>Please Come Again!</h3>
                            <p>We look forward to serving you again soon.</p>
                            <div class="action-buttons">
                                <a href="book_services.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-calendar-plus"></i> Book Another Appointment
                                </a>
                                <a href="client_dashboard.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-home"></i> Return to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>