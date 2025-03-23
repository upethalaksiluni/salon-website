<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$activePage = 'profile'; // For sidebar active state

try {
    // Get user details
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Sulochana Salon</title>
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

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Your existing profile styles */
        .profile-header {
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .info-label {
            color: #666;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .info-value {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        .action-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .section-title {
            color: #4a00e0;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'client_header.dat'; ?>
    <?php include 'client_sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <img src="<?php echo $user['profile_image'] ? $user['profile_image'] : 'assets/images/default-profile.jpg'; ?>" 
                             alt="Profile" class="profile-img mb-3">
                    </div>
                    <div class="col-md-9">
                        <h1 class="display-4 mb-0"><?php echo htmlspecialchars($user['fullname']); ?></h1>
                        <p class="lead mb-3">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                        <div class="d-flex gap-2">
                            <a href="edit_profile.php" class="btn btn-light action-btn">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                            <a href="client_dashboard.php" class="btn btn-outline-light action-btn">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                            <a href="logout.php" class="btn btn-danger action-btn">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="info-card">
                <h3 class="section-title"><i class="fas fa-user"></i> Personal Information</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="info-label">Gender</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['gender']); ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['birthdate']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="info-label">Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Address -->
            <div class="info-card">
                <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Address</h3>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($user['address'])); ?></div>
            </div>

            <!-- Service Preferences -->
            <div class="info-card">
                <h3 class="section-title"><i class="fas fa-heart"></i> Service Preferences</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="info-label">Preferred Stylist</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['preferred_stylist']); ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Frequent Services</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['frequent_services']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="info-label">Preferred Time</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['preferred_time']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Health Information -->
            <div class="info-card">
                <h3 class="section-title"><i class="fas fa-medkit"></i> Health Information</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="info-label">Allergies</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($user['allergies'])); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="info-label">Medical Conditions</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($user['medical_conditions'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'client_footer.dat'; ?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/client-sidebar.js"></script>
</body>
</html>