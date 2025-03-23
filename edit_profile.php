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

    if (isset($_POST['update_profile'])) {
        $uploadOk = true;
        $profile_image = $user['profile_image']; // Keep existing image by default

        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $target_dir = "uploads/profiles/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            $target_file = $target_dir . "user_" . $_SESSION['user_id'] . "_" . time() . "." . $imageFileType;

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
            if ($check === false) {
                $_SESSION['error_message'] = "File is not an image.";
                $uploadOk = false;
            }

            // Check file size
            if ($_FILES["profile_image"]["size"] > 5000000) {
                $_SESSION['error_message'] = "Sorry, your file is too large.";
                $uploadOk = false;
            }

            // Allow certain file formats
            if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                $_SESSION['error_message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = false;
            }

            if ($uploadOk) {
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                    $profile_image = $target_file;
                    // Delete old profile image if exists
                    if ($user['profile_image'] && file_exists($user['profile_image'])) {
                        unlink($user['profile_image']);
                    }
                } else {
                    $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                }
            }
        }

        $stmt = $conn->prepare("
            UPDATE user SET 
                fullname = ?,
                phone = ?,
                email = ?,
                address = ?,
                preferred_stylist = ?,
                frequent_services = ?,
                preferred_time = ?,
                allergies = ?,
                medical_conditions = ?,
                profile_image = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['fullname'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['address'],
            $_POST['preferred_stylist'],
            $_POST['frequent_services'],
            $_POST['preferred_time'],
            $_POST['allergies'],
            $_POST['medical_conditions'],
            $profile_image,
            $_SESSION['user_id']
        ]);

        $_SESSION['success_message'] = "Profile updated successfully!";
        header('Location: view_profile.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Sulochana Salon</title>
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

        .profile-upload {
            position: relative;
            width: 150px;
            margin: 0 auto;
        }

        .profile-upload img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4a00e0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .upload-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(74, 0, 224, 0.7);
            padding: 8px;
            color: white;
            text-align: center;
            cursor: pointer;
            border-bottom-left-radius: 75px;
            border-bottom-right-radius: 75px;
            transition: all 0.3s ease;
        }

        .upload-overlay:hover {
            background: rgba(74, 0, 224, 0.9);
            padding-top: 12px;
        }

        #profile_image {
            display: none;
        }

        .card {
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 15px;
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4a00e0;
            box-shadow: 0 0 0 0.2rem rgba(74, 0, 224, 0.25);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <?php include 'client_header.dat'; ?>
    <?php include 'client_sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    <div class="card">
                        <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #4a00e0, #8e2de2);">
                            <h3 class="card-title text-white mb-0">Edit Profile</h3>
                        </div>
                        <div class="card-body p-4">
                            <?php if (isset($_SESSION['error_message'])): ?>
                                <div class="alert alert-danger">
                                    <?php 
                                        echo $_SESSION['error_message'];
                                        unset($_SESSION['error_message']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="text-center mb-4">
                                    <div class="profile-upload">
                                        <img src="<?php echo $user['profile_image'] ? $user['profile_image'] : 'assets/images/default-profile.jpg'; ?>" 
                                             alt="Profile" id="preview-image">
                                        <label for="profile_image" class="upload-overlay">
                                            <i class="fas fa-camera"></i> Change Photo
                                        </label>
                                        <input type="file" name="profile_image" id="profile_image" 
                                               accept="image/*" onchange="previewImage(this)">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Full Name</label>
                                    <input type="text" name="fullname" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label>Phone</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control" rows="3"><?php 
                                        echo htmlspecialchars($user['address']); 
                                    ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label>Preferred Stylist</label>
                                    <input type="text" name="preferred_stylist" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['preferred_stylist']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label>Frequent Services</label>
                                    <input type="text" name="frequent_services" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['frequent_services']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label>Preferred Time</label>
                                    <input type="text" name="preferred_time" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['preferred_time']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label>Allergies</label>
                                    <textarea name="allergies" class="form-control" rows="2"><?php 
                                        echo htmlspecialchars($user['allergies']); 
                                    ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label>Medical Conditions</label>
                                    <textarea name="medical_conditions" class="form-control" rows="2"><?php 
                                        echo htmlspecialchars($user['medical_conditions']); 
                                    ?></textarea>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                    <a href="view_profile.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Profile
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'client_footer.dat'; ?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/client-sidebar.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('preview-image').setAttribute('src', e.target.result);
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>