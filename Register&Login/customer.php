<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../DB_connection.php";
session_start();

$error = '';
$success = '';
$account_type = 'customer'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = processRegistration($conn);
    if ($error === "Customer registration successful!") {
        $success = $error;
        $error = '';
    }
}

function validateEgyptPhone($phone) {
    return preg_match('/^01[0-9]{9}$/', $phone);
}

function validateName($name) {
    return preg_match('/^[a-zA-Z\s]+$/', $name);
}



function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function processRegistration($conn) {
    $requiredFields = ['fullname', 'email', 'phone', 'password', 'terms', 'address', 'gender', 'birthday'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            return "All fields are required";
        }
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
        return "Email already Exist";
    }

    if (!validateEgyptPhone($_POST['phone'])) {
        return "Please enter a valid phone number (10 or 11 digits starting with 01)";
    }

  
    $birthday = new DateTime($_POST['birthday']);
    $today = new DateTime();
    $age = $today->diff($birthday)->y;
    if ($age < 13) {
        return "You must be at least 13 years old to register";
    }

    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'external_user';

    try {
        $conn->begin_transaction();

        $sql = "INSERT INTO users (u_name, mail, phone, password, u_role) 
                VALUES ('$fullname', '$email', '$phone', '$password', '$role')";
        if (!$conn->query($sql)) throw new Exception("User insert failed: " . $conn->error);
        $user_id = $conn->insert_id;

        $address = $conn->real_escape_string($_POST['address']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $birthday = $conn->real_escape_string($_POST['birthday']);

        $sql = "INSERT INTO external_user (user_id, address, ext_role) 
                VALUES ('$user_id', '$address', 'customer')";
        if (!$conn->query($sql)) throw new Exception("External user insert failed: " . $conn->error);

        $sql = "INSERT INTO customer (user_id, gender, BOD) 
                VALUES ('$user_id', '$gender', '$birthday')";
        if (!$conn->query($sql)) throw new Exception("Customer insert failed: " . $conn->error);

        $conn->commit();
        return "Customer registration successful!";
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Registration Error: " . $e->getMessage());
        return "Registration failed. Please try again later.";
    }
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Meal - Customer Sign Up</title>
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
        <?php if ($error || $success): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const message = "<?php echo addslashes($success ? $success : $error); ?>";
                    const isSuccess = <?php echo $success ? 'true' : 'false'; ?>;
                    
                    const notification = document.createElement('div');
                    notification.className = `notification ${isSuccess ? 'success' : 'error'}`;
                    
                    const icon = document.createElement('i');
                    icon.setAttribute('data-lucide', isSuccess ? 'check-circle' : 'alert-circle');
                    notification.appendChild(icon);
                    
                    const text = document.createTextNode(message);
                    notification.appendChild(text);
                    
                    document.body.appendChild(notification);
                    lucide.createIcons();
                    
                    setTimeout(() => {
                        notification.style.opacity = '0';
                        notification.style.transition = 'opacity 0.5s ease';
                        
                        setTimeout(() => {
                            notification.remove();
                            if (isSuccess) {
                                window.location.href = 'login.php';
                            }
                        }, 500);
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
                <h1>Join as a Customer</h1>
                <p>Discover authentic Egyptian homemade meals prepared with love by talented housewives across Egypt.</p>
            </div>
            
            <div class="content-wrapper">
                <div class="image-container">
                    <div class="image"></div>
                </div>
                
                <div class="form-container">
                    <form class="signup-form-fields" method="POST" action="" enctype="multipart/form-data" id="registrationForm">
                        <input type="hidden" name="account-type" id="account-type" value="customer">

                        <div class="account-type">
                            <h2>Select Account Type</h2>
                            <div class="account-buttons">
                                <button type="button" class="account-btn active" 
                                        data-type="customer" onclick="window.location.href='customer.php'">
                                    <i data-lucide="user" class="icon"></i>
                                    <span>Customer</span>
                                </button>
                                <button type="button" class="account-btn" 
                                        data-type="caterer" onclick="window.location.href='cloud_kitchen.php'">
                                    <i data-lucide="chef-hat"></i>
                                    <span>Cloud Kitchen</span>
                                </button>
                                <button type="button" class="account-btn" 
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
                                    <i data-lucide="lock"></i> Password <span class="required">*</span>
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
                        
                        <div class="input-group-row">
                            <div class="input-group half-width">
                                <label for="gender">
                                    <i data-lucide="venetian-mask"></i> Gender <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <select id="gender" name="gender" required>
                                        <option value="" disabled hidden selected>Select Gender</option>
                                        <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                            </div>

                            <div class="input-group half-width">
                                <label for="birthday">
                                    <i data-lucide="calendar"></i> Birthday <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="date" id="birthday" name="birthday" required
                                           value="<?php echo htmlspecialchars($_POST['birthday'] ?? ''); ?>"
                                           max="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="address">
                                <i data-lucide="map-pin"></i> Address <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="address" name="address" placeholder="Enter your address" required
                                       value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="terms">
                            <label for="terms">
                                <input type="checkbox" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                                I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a>
                            </label>
                        </div>
                        <button type="submit" class="submit-btn">Create Customer Account</button>
                    </form>
                    <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
    <script> lucide.createIcons(); </script>
</body>
</html>