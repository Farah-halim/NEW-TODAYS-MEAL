<?php
session_start();
require_once("../DB_connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

if (!isset($_GET['kitchen_id']) || empty($_GET['kitchen_id'])) {
    $errorTitle = "Kitchen Not Selected";
    $errorHeading = "Oops! No Kitchen Selected";
    $errorMessage = "Please choose a kitchen from our selection to view their menu and place your custom order.";
    $errorLink = "\NEW-TODAYS-MEAL\customer\Show_Caterers\index.php";
    $errorLinkText = "Browse Kitchens";
    
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>'.htmlspecialchars($errorTitle).'</title>
        <link rel="stylesheet" href="error-styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        <div class="container">
            <div class="error-message">
                <h2>'.htmlspecialchars($errorHeading).'</h2>
                <p>'.htmlspecialchars($errorMessage).'</p>
                <a href="'.htmlspecialchars($errorLink).'">
                    <i class="fas fa-store"></i> '.htmlspecialchars($errorLinkText).'
                </a>
            </div>
        </div>
    </body>
    </html>';
    exit();
}

// Initialize variables
$errors = [];
$success = false;

// Initialize form fields with empty values
$orderDescription = '';
$budgetMin = '';
$budgetMax = '';
$deliveryDate = '';
$deliveryTime = '';
$guestCount = '';
$imagePath = null;

$kitchenId = isset($_GET['kitchen_id']) ? (int)$_GET['kitchen_id'] : 0;

// Check if the kitchen exists
$kitchenCheckQuery = "SELECT * FROM cloud_kitchen_owner WHERE user_id = ?";
$stmt = $conn->prepare($kitchenCheckQuery);
$stmt->bind_param("i", $kitchenId);
$stmt->execute();
$kitchenResult = $stmt->get_result();
$kitchenExists = $kitchenResult->num_rows > 0;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $orderDescription = $conn->real_escape_string(trim($_POST['orderDescription'] ?? ''));
    $budgetMin = (float)($_POST['budgetMin'] ?? 0);
    $budgetMax = (float)($_POST['budgetMax'] ?? 0);
    $deliveryDate = $conn->real_escape_string($_POST['deliveryDate'] ?? '');
    $deliveryTime = $conn->real_escape_string($_POST['deliveryTime'] ?? '');
    $guestCount = (int)($_POST['guestCount'] ?? 0);
    
    // Basic validation
    if (empty($orderDescription)) {
        $errors[] = "Order description is required";
    }
    
    if ($budgetMin <= 0) {
        $errors[] = "Minimum budget must be a positive number";
    }
    
    if ($budgetMax <= 0) {
        $errors[] = "Maximum budget must be a positive number";
    }
    
    if ($budgetMax < $budgetMin) {
        $errors[] = "Maximum budget must be greater than or equal to minimum budget";
    }
    
    if (empty($deliveryDate)) {
        $errors[] = "Delivery date is required";
    } elseif (strtotime($deliveryDate) < strtotime('today')) {
        $errors[] = "Delivery date cannot be in the past";
    }
    
    if (empty($deliveryTime)) {
        $errors[] = "Delivery time is required";
    }
    
    if ($guestCount <= 0) {
        $errors[] = "Number of guests must be a positive integer";
    }
    
    // Handle file upload
    if (isset($_FILES['referenceImage']) && $_FILES['referenceImage']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['referenceImage']['type'];
        $fileSize = $_FILES['referenceImage']['size'];

        if (in_array($fileType, $allowedTypes) && $fileSize <= 5 * 1024 * 1024) { // 5MB max
            $uploadDir = '../../uploads/custom_orders/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = pathinfo($_FILES['referenceImage']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('order_img_', true) . '.' . $extension;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['referenceImage']['tmp_name'], $destination)) {
                $imagePath = '../../uploads/custom_orders/' . $filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        } else {
            $errors[] = "Invalid file type or size (max 5MB allowed).";
        }
    } elseif (isset($_FILES['referenceImage']) && $_FILES['referenceImage']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "File upload error: " . $_FILES['referenceImage']['error'];
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // Insert into orders table
            $orderQuery = "INSERT INTO orders (
                customer_id, cloud_kitchen_id, total_price, order_date, 
                ord_type, delivery_type, delivery_date, delivery_zone, order_status
            ) VALUES (?, ?, ?, NOW(), 'customized', NULL, ?, 'default_zone', 'pending')";
            
            $stmt = $conn->prepare($orderQuery);
            $defaultPrice = 0;
            $deliveryDateTime = date("Y-m-d H:i:s", strtotime("$deliveryDate $deliveryTime"));
            $stmt->bind_param("iids", $_SESSION['user_id'], $kitchenId, $defaultPrice, $deliveryDateTime);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating order: " . $conn->error);
            }
            
            $orderId = $conn->insert_id;
            
            // Insert into customized_order table
            $customOrderQuery = "INSERT INTO customized_order (
                order_id, customer_id, kitchen_id, budget_min, 
                budget_max, chosen_amount, status, ord_description, 
                img_reference, people_servings, preferred_completion_date, created_at,
                customer_approval
            ) VALUES (?, ?, ?, ?, ?, NULL, 'pending', ?, ?, ?, ?, NOW(), 'pending')";
            
            $stmt = $conn->prepare($customOrderQuery);
            $stmt->bind_param(
                "iiiddssis",
                $orderId,
                $_SESSION['user_id'],
                $kitchenId,
                $budgetMin,
                $budgetMax,
                $orderDescription,
                $imagePath,
                $guestCount,
                $deliveryDateTime
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error saving custom order details: " . $conn->error);
            }
            
            $conn->commit();
            $success = true; // Set success to true to show popup
            
            // Clear form fields on success
            $orderDescription = '';
            $budgetMin = '';
            $budgetMax = '';
            $deliveryDate = '';
            $deliveryTime = '';
            $guestCount = '';
            $imagePath = null;
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Order Form</title>
    <link rel="stylesheet" href="custom-order.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../global/navbar/navbar.php'; ?>

        <div class="container">
        <div class="form-wrapper">
            <div class="form-card">
                <h1 class="form-title">Custom Order Form</h1>

                <form class="order-form" method="POST" enctype="multipart/form-data">
                    <div class="form-section">
                        <label class="form-label">
                            <span class="icon"><i class="fas fa-pen"></i></span>
                            Order Description
                        </label>
                        <textarea name="orderDescription" placeholder="Please describe your order in detail..." class="form-textarea" rows="5" minlength="10" required><?php echo htmlspecialchars($orderDescription); ?></textarea>
                    </div>
                    <div class="form-section">
                        <label class="form-label">
                            <span class="icon">$</span>
                            Budget Range (EGP)
                        </label>
                        <div class="budget-range">
                            <input type="number" name="budgetMin" placeholder="Min" class="budget-input" min="1" step="0.01" required value="<?php echo htmlspecialchars($budgetMin); ?>">
                            <span class="separator">-</span>
                            <input type="number" name="budgetMax" placeholder="Max" class="budget-input" min="1" step="0.01" required value="<?php echo htmlspecialchars($budgetMax); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-section">
                            <label class="form-label">
                                <span class="icon"><i class="fas fa-truck"></i></span>
                                Delivery Date
                            </label>
                            <input type="date" name="deliveryDate" class="form-input" required value="<?php echo htmlspecialchars($deliveryDate); ?>">
                        </div>
                        <div class="form-section">
                            <label class="form-label">
                                <span class="icon"><i class="fas fa-clock"></i></span>
                                Delivery Time
                            </label>
                            <input type="time" name="deliveryTime" class="form-input" required value="<?php echo htmlspecialchars($deliveryTime); ?>">
                        </div>
                    </div>
                    <div class="form-section">
                        <label class="form-label">
                            <span class="icon"><i class="fas fa-users"></i></span>
                            Number of People to Serve
                        </label>
                        <input type="number" name="guestCount" placeholder="Enter number of guests" class="form-input" min="1" required value="<?php echo htmlspecialchars($guestCount); ?>">
                    </div>

                    <div class="form-section">
                        <label class="form-label">
                            <span class="icon"><i class="fas fa-image"></i></span>
                            Upload Reference Image (Optional)
                        </label>
                        <label class="upload-area">
                            <input type="file" name="referenceImage" accept="image/jpeg,image/png,image/gif" onchange="showFileName(this)">
                            <div class="upload-content">
                                <span class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                <p class="upload-text">Click to upload an image</p>
                                <p class="upload-subtext">JPG, PNG or GIF (max. 5MB)</p>
                            </div>
                        </label>
                    </div>

                    <div class="button-group">
                        <a href="../Show Menu_Codes/index.php?kitchen_id=<?php echo $kitchenId; ?>" class="btn btn-secondary">Back to Menu</a>
                        <button type="submit" class="btn btn-primary">Submit Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Success Popup -->
    <div id="success-popup" class="success-popup" style="display: none;">
        <i class="fas fa-check-circle"></i> Your custom order has been submitted successfully!
    </div>

    <script>
            function showFileName(input) {
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                const uploadContent = input.nextElementSibling;
                uploadContent.innerHTML = `
                    <span class="upload-icon"></span>
                    <p class="upload-text">${fileName}</p>
                    <p class="upload-subtext">Click to change file</p>
                `;
            }
        }

        // Show success popup if submission was successful
        <?php if ($success): ?>
            document.addEventListener("DOMContentLoaded", function() {
                const popup = document.getElementById('success-popup');
                popup.style.display = "block";
                
                // Hide after 4 seconds
                setTimeout(() => {
                    popup.style.display = "none";
                }, 4000);
                
                // Optionally scroll to top to ensure popup is visible
                window.scrollTo(0, 0);
            });
        <?php endif; ?>setTimeout(() => {
            popup.style.animation = "fadeOut 0.5s ease-out";
            setTimeout(() => {
                popup.style.display = "none";
            }, 500);
        }, 3500);

    </script>
</body>
</html>