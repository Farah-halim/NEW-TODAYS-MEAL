<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../DB_connection.php";
session_start();

$error = '';
$account_type = 'delivery'; // Fixed for this page

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = processRegistration($conn);
}

function processRegistration($conn) {
    $requiredFields = ['fullname', 'email', 'phone', 'password', 'terms', 'deliveryNationalId', 'delivery-zone'];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            return "Missing required field: $field";
        }
    }

    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'delivery_man';

    try {
        $conn->begin_transaction();

        $sql = "INSERT INTO users (u_name, mail, phone, password, u_role) VALUES ('$fullname', '$email', '$phone', '$password', '$role')";
        if (!$conn->query($sql)) throw new Exception("User insert failed: " . $conn->error);
        $user_id = $conn->insert_id;

        $deliveryNationalId = $conn->real_escape_string($_POST['deliveryNationalId']);
        $deliveryZone = $conn->real_escape_string($_POST['delivery-zone']);
        $licenseFile = uploadLicense();

        $sql = "INSERT INTO delivery_man (user_id, d_n_id, d_license, d_zone) VALUES ('$user_id', '$deliveryNationalId', '$licenseFile', '$deliveryZone')";
        if (!$conn->query($sql)) throw new Exception("Delivery man insert failed: " . $conn->error);

        return "Delivery account registration successful!";
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Registration Error: " . $e->getMessage());
        return $e->getMessage();
    } finally {
        $conn->commit();
    }
}

function uploadLicense() {
    if ($_FILES['license']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("License file upload error: " . $_FILES['license']['error']);
    }

    $allowed_types = ['application/pdf', 'image/png', 'image/jpeg'];
    if (!in_array($_FILES['license']['type'], $allowed_types)) {
        throw new Exception("Invalid file type. Only PDF, PNG, and JPG are allowed");
    }

    $target_dir = "../uploads/delivery_liscense/";
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    $file_ext = pathinfo($_FILES['license']['name'], PATHINFO_EXTENSION);
    $licenseFile = uniqid() . '.' . $file_ext;
    $target_path = $target_dir . $licenseFile;

    if (!move_uploaded_file($_FILES['license']['tmp_name'], $target_path)) {
        throw new Exception("Failed to move uploaded file");
    }

    return $licenseFile;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Meal - Delivery Sign Up</title>
    <link rel="stylesheet" href="global-register.css" />
    <link rel="stylesheet" href="register.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="signup-form">
        <?php if ($error && $error !== "Delivery account registration successful!"): ?>
            <div class="error-message" style="color: red; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($error === "Delivery account registration successful!"): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const messageDiv = document.createElement('div');
                    messageDiv.textContent = "Delivery account registration successful! Redirecting to login...";
                    messageDiv.style.position = 'fixed';
                    messageDiv.style.top = '20px';
                    messageDiv.style.right = '20px';
                    messageDiv.style.backgroundColor = '#4CAF50';
                    messageDiv.style.color = 'white';
                    messageDiv.style.padding = '15px';
                    messageDiv.style.borderRadius = '8px';
                    messageDiv.style.zIndex = '9999';
                    document.body.appendChild(messageDiv);

                    setTimeout(function () {
                        window.location.href = 'login.php';
                    }, 3000);
                });
            </script>
        <?php endif; ?>

        <nav class="navbar">
            <div class="nav-content">
                <a href="#" class="logo-link">
                    <img src="logo.png" alt="Today's Meal" class="logo">
                </a>
                <div class="nav-links">
                    <a href="#" class="nav-link">Home</a>
                    <a href="#" class="nav-link">About</a>
                    <a href="#" class="nav-link">Contact</a>
                </div>
            </div>
        </nav>
        
        <div class="main-content">
            <div class="header">
                <h1>Join Our Delivery Team</h1>
                <p>Become part of our delivery network and help connect customers with authentic Egyptian homemade meals.</p>
            </div>
            
            <div class="content-wrapper">
                <div class="image-container">
                    <div class="image"></div>
                </div>
                
                <div class="form-container">
                    <form class="signup-form-fields" method="POST" action="" enctype="multipart/form-data" id="registrationForm">
                        <input type="hidden" name="account-type" id="account-type" value="delivery">
                        
                        <div class="account-type">
                            <h2>Select Account Type</h2>
                            <div class="account-buttons">
                                <button type="button" class="account-btn" 
                                        data-type="customer" onclick="window.location.href='customer.php'">
                                    <i data-lucide="user" class="icon"></i>
                                    <span>Customer</span>
                                </button>
                                <button type="button" class="account-btn" 
                                        data-type="caterer" onclick="window.location.href='cloud_kitchen.php'">
                                    <i data-lucide="chef-hat"></i>
                                    <span>Cloud Kitchen</span>
                                </button>
                                <button type="button" class="account-btn active" 
                                        data-type="delivery" onclick="window.location.href='delivery.php'">
                                    <i data-lucide="truck"></i>
                                    <span>Delivery</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="input-group-row">
                            <div class="input-group half-width">
                                <label for="fullname" class="input-text">
                                    <i data-lucide="user"></i> Full Name <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required
                                           value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="input-group half-width">
                                <label for="email">
                                    <i data-lucide="mail"></i> Email Address <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="email" id="email" name="email" placeholder="Enter your email address" required
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="input-group-row">
                            <div class="input-group half-width">
                                <label for="password"> 
                                    <i data-lucide="lock"></i>
                                    Password <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                                </div>
                            </div>

                            <div class="input-group half-width">
                                <label for="phone">
                                    <i data-lucide="phone"></i> Phone Number <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="delivery-zone">
                                <i data-lucide="map-pin"></i> Zone <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="delivery-zone" name="delivery-zone" placeholder="Enter your zone" required
                                       value="<?php echo htmlspecialchars($_POST['delivery-zone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="input-group full-width">
                            <label for="deliveryNationalId">
                                <i data-lucide="credit-card"></i> National ID <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="deliveryNationalId" name="deliveryNationalId" placeholder="Enter your National ID" required
                                       value="<?php echo htmlspecialchars($_POST['deliveryNationalId'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="input-group full-width">
                            <label for="license">Driver's License <span class="required">*</span></label>
                            <div class="file-upload">
                                <label for="license" class="file-label">
                                    <i data-lucide="upload"></i>
                                    <span>PDF, PNG, JPG (MAX. 2MB)</span>
                                </label>
                                <input type="file" id="license" name="license" accept=".pdf,.png,.jpg,image/jpeg" required>
                            </div>
                        </div>
                        
                        <div class="terms">
                            <label for="terms">
                                <input type="checkbox" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                                I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a>
                            </label>
                        </div>
                        <button type="submit" class="submit-btn">Create Delivery Account</button>
                    </form>
                    <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>