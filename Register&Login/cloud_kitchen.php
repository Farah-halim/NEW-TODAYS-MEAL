<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../DB_connection.php";
session_start();

$error = '';
$success = '';
$account_type = 'caterer'; // Fixed for this page

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = processRegistration($conn);
    if (strpos($response, 'successful') !== false) {
        $error = $response; // Used to trigger redirect
    } else {
        $error = $response;
    }
}

function processRegistration($conn) {
    $requiredFields = ['fullname', 'email', 'phone', 'password', 'terms', 'address', 'businessName', 'nationalId', 'experience', 'custom-orders'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            return "Missing required field: $field";
        }
    }

    if (empty($_POST['cuisine']) || !is_array($_POST['cuisine'])) {
        return "Please select at least one cuisine specialty";
    }

    // Check required documents
    $requiredDocuments = ['national_id_doc', 'business_license_doc'];
    foreach ($requiredDocuments as $docField) {
        if (!isset($_FILES[$docField]) || $_FILES[$docField]['error'] !== UPLOAD_ERR_OK) {
            return "Please upload all required documents (National ID and Business License)";
        }
    }

    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
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
        $c_n_id = $conn->real_escape_string($_POST['nationalId']);
        $experience = $conn->real_escape_string($_POST['experience']);
        $customized_orders = ($_POST['custom-orders'] === 'yes') ? 1 : 0;

        // Get the first selected cuisine as speciality_id
        $speciality_id = (int)$_POST['cuisine'][0];

        $sql = "INSERT INTO external_user (user_id, address, ext_role, latitude, longitude, zone_id) VALUES ('$user_id', '$address', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 2)";
        if (!$conn->query($sql)) throw new Exception("External user insert failed: " . $conn->error);

        $sql = "INSERT INTO cloud_kitchen_owner (user_id, business_name, c_n_id, years_of_experience, customized_orders, start_year, speciality_id, is_approved) VALUES 
                ('$user_id', '$business_name', '$c_n_id', '$experience', '$customized_orders', YEAR(NOW()), '$speciality_id', 0)";
        if (!$conn->query($sql)) throw new Exception("Kitchen owner insert failed: " . $conn->error);

        foreach ($_POST['cuisine'] as $cat_id) {
            $cat_id = (int)$conn->real_escape_string($cat_id);
            if ($cat_id > 0) {
                $sql = "INSERT INTO cloud_kitchen_specialist_category (cloud_kitchen_id, cat_id) VALUES ('$user_id', '$cat_id')";
                if (!$conn->query($sql)) throw new Exception("Failed to insert cuisine specialty: " . $conn->error);
            }
        }

        // Handle document uploads
        $uploadResult = handleDocumentUploads($user_id, $conn);
        if ($uploadResult !== true) {
            throw new Exception($uploadResult);
        }

        $conn->commit();
        return "Cloud Kitchen registration successful! Your documents have been uploaded and are pending admin review.";
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Registration Error: " . $e->getMessage());
        return $e->getMessage();
    }
}

function handleDocumentUploads($kitchen_id, $conn) {
    // Create uploads directory structure
    $baseUploadDir = "../admin/c/c/uploads/documents/" . $kitchen_id . "/";
    if (!file_exists($baseUploadDir)) {
        if (!mkdir($baseUploadDir, 0755, true)) {
            return "Failed to create upload directory";
        }
    }

    $documents = [
        'national_id_doc' => [
            'type' => 'national_id',
            'name' => 'National ID',
            'required' => true
        ],
        'business_license_doc' => [
            'type' => 'business_license', 
            'name' => 'Business License',
            'required' => true
        ],
        'health_cert_doc' => [
            'type' => 'health_certificate',
            'name' => 'Health Certificate',
            'required' => false
        ],
        'tax_cert_doc' => [
            'type' => 'tax_certificate',
            'name' => 'Tax Certificate', 
            'required' => false
        ],
        'kitchen_photos_doc' => [
            'type' => 'kitchen_photos',
            'name' => 'Kitchen Photos',
            'required' => false
        ]
    ];

    foreach ($documents as $fieldName => $docInfo) {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            if ($docInfo['required']) {
                return "Required document missing: " . $docInfo['name'];
            }
            continue;
        }

        $file = $_FILES[$fieldName];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return "Upload error for " . $docInfo['name'] . ": " . $file['error'];
        }

        // Validate file type
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowedTypes)) {
            return "Invalid file type for " . $docInfo['name'] . ". Please upload PDF, JPEG, or PNG files only.";
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return "File too large for " . $docInfo['name'] . ". Maximum size is 5MB.";
        }

        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeFileName = strtolower($docInfo['type']) . '_' . time() . '.' . $extension;
        $targetPath = $baseUploadDir . $safeFileName;
        $relativePath = "uploads/documents/" . $kitchen_id . "/" . $safeFileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return "Failed to save " . $docInfo['name'];
        }

        // Insert document record into database
        $sql = "INSERT INTO kitchen_documents (kitchen_id, document_type, document_name, file_path, file_size, file_type, upload_date) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $kitchen_id, $docInfo['type'], $docInfo['name'], $relativePath, $file['size'], $file['type']);
        
        if (!$stmt->execute()) {
            return "Failed to save document record for " . $docInfo['name'] . ": " . $stmt->error;
        }
    }

    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Meal - Cloud Kitchen Sign Up</title>
    <link rel="stylesheet" href="global-register.css" />
    <link rel="stylesheet" href="register.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="signup-form">
        <?php if ($error && $error !== "Cloud Kitchen registration successful!"): ?>
            <div class="error-message" style="color: red; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($error === "Cloud Kitchen registration successful!"): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const messageDiv = document.createElement('div');
                    messageDiv.textContent = "Cloud Kitchen registration successful! Redirecting to login...";
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
                <h1>Join as a Cloud Kitchen</h1>
                <p>Showcase your culinary skills and connect with customers who appreciate authentic homemade meals.</p>
            </div>
            
            <div class="content-wrapper">
                <div class="image-container">
                    <div class="image"></div>
                </div>
                
                <div class="form-container">
                    <form class="signup-form-fields" method="POST" action="" enctype="multipart/form-data" id="registrationForm">
                        <input type="hidden" name="account-type" id="account-type" value="caterer">
                        
                        <div class="account-type">
                            <h2>Select Account Type</h2>
                            <div class="account-buttons">
                                <button type="button" class="account-btn" 
                                        data-type="customer" onclick="window.location.href='customer.php'">
                                    <i data-lucide="user" class="icon"></i>
                                    <span>Customer</span>
                                </button>
                                <button type="button" class="account-btn active" 
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
                            <label for="address">
                                <i data-lucide="map-pin"></i> Address <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="address" name="address" placeholder="Enter your address" required
                                       value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="nationalId">
                                <i data-lucide="credit-card"></i> National ID <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="nationalId" name="nationalId" placeholder="Enter your National ID" required
                                       value="<?php echo htmlspecialchars($_POST['nationalId'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="businessName">
                                <i data-lucide="briefcase-business"></i> Business Name <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="businessName" name="businessName" placeholder="Your business name" required
                                       value="<?php echo htmlspecialchars($_POST['businessName'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="input-group full-width">
                            <label for="experience">
                                <i data-lucide="award"></i> Years of Experience <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select id="experience" name="experience" required>
                                    <option value="" disabled selected>Select experience level</option>
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
                                    <input type="checkbox" value="<?php echo $row['cat_id']; ?>" name="cuisine[]" id="cuisine_<?php echo $row['cat_id']; ?>" <?php echo $checked; ?>>
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
                                    <option value="" disabled selected>Select option</option>
                                    <option value="yes" <?php echo ($_POST['custom-orders'] ?? '') === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                    <option value="no" <?php echo ($_POST['custom-orders'] ?? '') === 'no' ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- Document Upload Section -->
                        <div class="document-section">
                            <h3><i data-lucide="file-text"></i> Required Documents</h3>
                            <p class="document-info">Upload the following documents for verification. All documents must be in PDF, JPEG, or PNG format (max 5MB each).</p>
                            
                            <div class="input-group full-width">
                                <label for="national_id_doc">
                                    <i data-lucide="credit-card"></i> National ID Document <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="file" id="national_id_doc" name="national_id_doc" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="file-help">Upload a clear photo or scan of your National ID</small>
                                </div>
                            </div>

                            <div class="input-group full-width">
                                <label for="business_license_doc">
                                    <i data-lucide="briefcase"></i> Business License Document <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="file" id="business_license_doc" name="business_license_doc" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="file-help">Upload your official business license or registration certificate</small>
                                </div>
                            </div>

                            <div class="input-group full-width">
                                <label for="health_cert_doc">
                                    <i data-lucide="shield-check"></i> Health Certificate (Optional)
                                </label>
                                <div class="input-wrapper">
                                    <input type="file" id="health_cert_doc" name="health_cert_doc" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="file-help">Upload health department certification if available</small>
                                </div>
                            </div>

                            <div class="input-group full-width">
                                <label for="tax_cert_doc">
                                    <i data-lucide="receipt"></i> Tax Certificate (Optional)
                                </label>
                                <div class="input-wrapper">
                                    <input type="file" id="tax_cert_doc" name="tax_cert_doc" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="file-help">Upload tax registration certificate if available</small>
                                </div>
                            </div>

                            <div class="input-group full-width">
                                <label for="kitchen_photos_doc">
                                    <i data-lucide="camera"></i> Kitchen Photos (Optional)
                                </label>
                                <div class="input-wrapper">
                                    <input type="file" id="kitchen_photos_doc" name="kitchen_photos_doc" accept=".jpg,.jpeg,.png" multiple>
                                    <small class="file-help">Upload photos of your kitchen workspace</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="terms">
                            <label for="terms">
                                <input type="checkbox" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                                I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a>
                            </label>
                        </div>
                        <button type="submit" class="submit-btn">Create Cloud Kitchen Account</button>
                    </form>
                    <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>
                    
                    <div class="additional-links">
                        <p class="status-link">Already registered? Check your document status:</p>
                        <a href="documents_status.php" class="document-status-btn">
                            <i data-lucide="file-check"></i>
                            View Document Status & Admin Feedback
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .document-section {
            margin: 2rem 0;
            padding: 1.5rem;
            border: 2px dashed #e57e24;
            border-radius: 8px;
            background-color: #fdf9f5;
        }
        
        .document-section h3 {
            color: #e57e24;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .document-info {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }
        
        .file-help {
            display: block;
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            font-style: italic;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        
        input[type="file"]:hover {
            border-color: #e57e24;
        }
        
        input[type="file"]:focus {
            outline: none;
            border-color: #e57e24;
            box-shadow: 0 0 0 2px rgba(229, 126, 36, 0.2);
        }
        
        .required {
            color: #dc3545;
        }
        
        .additional-links {
            margin-top: 1.5rem;
            padding: 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e9ecef;
            border-radius: 8px;
            text-align: center;
        }
        
        .status-link {
            margin: 0 0 1rem 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .document-status-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(23, 162, 184, 0.2);
        }
        
        .document-status-btn:hover {
            background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
            color: white;
        }
        
        .document-status-btn i {
            width: 18px;
            height: 18px;
        }
    </style>
    
    <script>
        lucide.createIcons();
        
        // File upload validation
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        // Check file size (5MB limit)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('File size must be less than 5MB');
                            this.value = '';
                            return;
                        }
                        
                        // Check file type
                        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                        if (!allowedTypes.includes(file.type)) {
                            alert('Please upload only PDF, JPEG, or PNG files');
                            this.value = '';
                            return;
                        }
                    }
                });
            });
            
            // Form submission validation
            const form = document.getElementById('registrationForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const requiredDocs = ['national_id_doc', 'business_license_doc'];
                    let allRequiredUploaded = true;
                    
                    requiredDocs.forEach(docName => {
                        const input = document.querySelector(`input[name="${docName}"]`);
                        if (!input || !input.files.length) {
                            allRequiredUploaded = false;
                        }
                    });
                    
                    if (!allRequiredUploaded) {
                        e.preventDefault();
                        alert('Please upload all required documents (National ID and Business License)');
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>