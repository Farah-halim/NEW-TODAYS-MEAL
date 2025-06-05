<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../DB_connection.php";
session_start();

$error = '';
$success = '';
$account_type = 'caterer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = processRegistration($conn);
    if (strpos($response, 'successful') !== false) {
        $success = $response;
    } else {
        $error = $response;
    }
}

function validateEgyptPhone($phone) {
    return preg_match('/^01[0-9]{9}$/', $phone);
}

function validateName($name) {
    return preg_match('/^[a-zA-Z\s]+$/', $name);
}

function validatePassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $password);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateEgyptNationalID($nid) {
    if (!preg_match('/^\d{14}$/', $nid)) {
        return false;
    }
    $centuryDigit = intval(substr($nid, 2, 1));
    if ($centuryDigit !== 2 && $centuryDigit !== 3) {
        return false;
    }
    return true;
}

function processRegistration($conn) {
    $requiredFields = ['fullname', 'email', 'phone', 'password', 'terms', 'address', 'businessName', 'nationalId', 'experience', 'custom-orders'];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            return "Missing required field: $field";
        }
    }

    if (empty($_POST['cuisine']) || !is_array($_POST['cuisine']) || count($_POST['cuisine']) === 0) {
        return "Please select at least one cuisine specialty";
    }

    if (!validateName($_POST['fullname'])) {
        return "Name should contain only letters and spaces";
    }

    if (!validateEmail($_POST['email'])) {
        return "Please enter a valid email address";
    }

    $email = $conn->real_escape_string($_POST['email']);
    $sql = "SELECT mail FROM users WHERE mail = '$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return "Email already exists";
    }

    if (!validateEgyptPhone($_POST['phone'])) {
        return "Please enter a valid Egyptian phone number starting with 01 followed by 9 digits";
    }

    if (!validatePassword($_POST['password'])) {
        return "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number";
    }

    $nationalId = trim($_POST['nationalId']);
    if (!validateEgyptNationalID($nationalId)) {
        return "Please enter a valid 14-digit Egyptian National ID";
    }

    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'external_user';

    try {
        $conn->begin_transaction();

        $sql = "INSERT INTO users (u_name, mail, phone, password, u_role) VALUES ('$fullname', '$email', '$phone', '$password', '$role')";
        if (!$conn->query($sql)) throw new Exception("User insert failed: " . $conn->error);
        $user_id = $conn->insert_id;

        $address = $conn->real_escape_string($_POST['address']);
        $business_name = $conn->real_escape_string($_POST['businessName']);
        $c_n_id = $conn->real_escape_string($nationalId);
        $experience = $conn->real_escape_string($_POST['experience']);
        $customized_orders = ($_POST['custom-orders'] === 'yes') ? 1 : 0;

        $sql = "INSERT INTO external_user (user_id, address, ext_role) VALUES ('$user_id', '$address', 'cloud_kitchen_owner')";
        if (!$conn->query($sql)) throw new Exception("External user insert failed: " . $conn->error);

        $sql = "INSERT INTO cloud_kitchen_owner (user_id, business_name, c_n_id, years_of_experience, customized_orders, start_year) VALUES 
                ('$user_id', '$business_name', '$c_n_id', '$experience', '$customized_orders', YEAR(NOW()))";
        if (!$conn->query($sql)) throw new Exception("Kitchen owner insert failed: " . $conn->error);

        foreach ($_POST['cuisine'] as $cat_id) {
            $cat_id = (int)$conn->real_escape_string($cat_id);
            if ($cat_id > 0) {
                $sql = "INSERT INTO cloud_kitchen_specialist_category (cloud_kitchen_id, cat_id) VALUES ('$user_id', '$cat_id')";
                if (!$conn->query($sql)) throw new Exception("Failed to insert cuisine specialty: " . $conn->error);
            }
        }

        $conn->commit();
        return "Cloud Kitchen registration successful!";
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Registration Error: " . $e->getMessage());
        return "Registration failed: " . $e->getMessage();
    }
}  ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Today's Meal - Cloud Kitchen Sign Up</title>
    <link rel="stylesheet" href="global-register.css" />
    <link rel="stylesheet" href="register.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 8px;
            z-index: 9999;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            animation: slideIn 0.3s forwards;
        }
        
        .notification.success {
            background-color: #4CAF50;
        }
        
        .notification.error {
            background-color: #f44336;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="signup-form">
        <?php if ($error): ?>
            <div class="notification error" id="notification">
                <i data-lucide="alert-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <script>
                setTimeout(() => {
                    const notice = document.getElementById('notification');
                    if (notice) {
                        notice.style.transition = 'opacity 0.5s ease';
                        notice.style.opacity = '0';
                        setTimeout(() => notice.remove(), 500);
                    }
                }, 3000);
            </script>
        <?php elseif ($success): ?>
            <div class="notification success" id="notification">
                <i data-lucide="check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
            <script>
                setTimeout(() => {
                    const notice = document.getElementById('notification');
                    if (notice) {
                        notice.style.transition = 'opacity 0.5s ease';
                        notice.style.opacity = '0';
                        setTimeout(() => notice.remove(), 500);
                    }
                }, 3000);
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3500);
            </script>
        <?php endif; ?>

        <nav class="navbar">
            <div class="nav-content">
                <a href="#" class="logo-link">
                    <img src="logo.png" alt="Today's Meal" class="logo" />
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
                <h1>Join as a Cloud Kitchen</h1>
                <p>Showcase your culinary skills and connect with customers who appreciate authentic homemade meals.</p>
            </div>
            
            <div class="content-wrapper">
                <div class="image-container">
                    <div class="image"></div>
                </div>
                
                <div class="form-container">
                    <form class="signup-form-fields" method="POST" action="" enctype="multipart/form-data" id="registrationForm" novalidate>
                        <input type="hidden" name="account-type" id="account-type" value="caterer" />
                        
                        <div class="account-type">
                            <h2>Select Account Type</h2>
                            <div class="account-buttons">
                                <button type="button" class="account-btn" data-type="customer" onclick="window.location.href='customer.php'">
                                    <i data-lucide="user" class="icon"></i>
                                    <span>Customer</span>
                                </button>
                                <button type="button" class="account-btn active" data-type="caterer" onclick="window.location.href='cloud_kitchen.php'">
                                    <i data-lucide="chef-hat"></i>
                                    <span>Cloud Kitchen</span>
                                </button>
                                <button type="button" class="account-btn" data-type="delivery" onclick="window.location.href='delivery.php'">
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
                                    <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <div class="input-group half-width">
                                <label for="email">
                                    <i data-lucide="mail"></i> Email Address <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="email" id="email" name="email" placeholder="Enter your email address" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
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
                                    <input type="password" id="password" name="password" placeholder="Create a password" required />
                                </div>
                            </div>
                        
                            <div class="input-group half-width">
                                <label for="phone">
                                    <i data-lucide="phone"></i> Phone Number <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="address">
                                <i data-lucide="map-pin"></i> Address <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="address" name="address" placeholder="Enter your address" required value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" />
                            </div>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="nationalId">
                                <i data-lucide="credit-card"></i> National ID <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="nationalId" name="nationalId" placeholder="Enter your National ID" required pattern="\d{14}" title="Please enter a 14-digit Egyptian National ID" value="<?php echo htmlspecialchars($_POST['nationalId'] ?? ''); ?>" />
                            </div>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="businessName">
                                <i data-lucide="briefcase-business"></i> Business Name <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="businessName" name="businessName" placeholder="Your business name" required value="<?php echo htmlspecialchars($_POST['businessName'] ?? ''); ?>" />
                            </div>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="experience">
                                <i data-lucide="award"></i> Years of Experience <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select id="experience" name="experience" required>
                                    <option value="" disabled <?php echo !isset($_POST['experience']) ? 'selected' : ''; ?>>Select experience level</option>
                                    <option value="Beginner (0-1 years)" <?php echo ($_POST['experience'] ?? '') === 'Beginner (0-1 years)' ? 'selected' : ''; ?>>Beginner (0-1 years)</option>
                                    <option value="Intermediate (2-3 years)" <?php echo ($_POST['experience'] ?? '') === 'Intermediate (2-3 years)' ? 'selected' : ''; ?>>Intermediate (2-3 years)</option>
                                    <option value="Advanced (4-5 years)" <?php echo ($_POST['experience'] ?? '') === 'Advanced (4-5 years)' ? 'selected' : ''; ?>>Advanced (4-5 years)</option>
                                    <option value="Expert (6+ years)" <?php echo ($_POST['experience'] ?? '') === 'Expert (6+ years)' ? 'selected' : ''; ?>>Expert (6+ years)</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group full-width">
                            <label for="cuisine">
                                <i data-lucide="utensils"></i> Cuisine Specialties <span class="required">*</span>
                            </label>
                            <div class="input-wrapper" style="max-width: 86%; max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                <?php
                                $result = $conn->query("SELECT cat_id, c_name FROM category");
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()):
                                        $checked = isset($_POST['cuisine']) && in_array($row['cat_id'], $_POST['cuisine']) ? 'checked' : '';
                                ?>
                                <label class="checkbox-option">
                                    <input type="checkbox" value="<?php echo $row['cat_id']; ?>" name="cuisine[]" id="cuisine_<?php echo $row['cat_id']; ?>" <?php echo $checked; ?> />
                                    <span class="checkmark"></span>
                                    <?php echo htmlspecialchars($row['c_name']); ?>
                                </label>
                                <?php 
                                    endwhile;
                                } else {
                                    echo '<p>No cuisine categories available</p>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="input-group full-width">
                            <label for="custom-orders">
                                <i data-lucide="check-circle"></i> Provide Customized Orders? <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select id="custom-orders" name="custom-orders" required>
                                    <option value="" disabled <?php echo !isset($_POST['custom-orders']) ? 'selected' : ''; ?>>Select option</option>
                                    <option value="yes" <?php echo ($_POST['custom-orders'] ?? '') === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                    <option value="no" <?php echo ($_POST['custom-orders'] ?? '') === 'no' ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="terms">
                            <label for="terms">
                                <input type="checkbox" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> />
                                I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a>
                            </label>
                        </div>
                        <button type="submit" class="submit-btn">Create Cloud Kitchen Account</button>
                    </form>
                    <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
    <script> lucide.createIcons();  </script>
</body>
</html>