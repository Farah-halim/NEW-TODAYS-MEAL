<?php
session_start();
require_once('../../DB_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if (!isset($_SESSION['cart_id'])) {
    $_SESSION['redirect_reason'] = "Your cart session expired or wasn't properly initialized.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}

// Variable Initialization
$errors = [];
$successMessage = '';
$firstName = $email = $phone = $address = '';
$isSubscribed = false;
$cartItems = [];
$subtotal = $deliveryFees = $total = 0;

// Get user details
$query = "SELECT u.u_name, u.mail, u.phone, eu.address, c.is_subscribed
          FROM users u
          JOIN external_user eu ON u.user_id = eu.user_id
          LEFT JOIN customer c ON eu.user_id = c.user_id
          WHERE u.user_id = ? AND eu.ext_role = 'customer'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $firstName = $user['u_name'];
    $email = $user['mail'];
    $phone = $user['phone'];
    $address = $user['address'];
    $isSubscribed = $user['is_subscribed'];
} else {
    $errors[] = "User not found or not a customer";
}
$stmt->close();

// Get cart items for order summary display
$query = "SELECT ci.*, m.name as meal_name, m.price, m.photo 
          FROM cart_items ci
          JOIN meals m ON ci.meal_id = m.meal_id
          WHERE ci.cart_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['cart_id']);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cartItems)) {
    $_SESSION['redirect_reason'] = "Your cart is empty.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}

// Calculate subtotal
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Delivery Fees Calculation
$deliveryFees = 15.00;
if ($isSubscribed) {
    $query = "SELECT * FROM delivery_subscriptions 
              WHERE customer_id = ? AND is_active = 1 AND end_date >= CURDATE()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $deliveryFees = 0.00;
    }
    $stmt->close();
}

$total = $subtotal + $deliveryFees;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['place-order'])) {
        $orderType = 'normal';
        $deliveryZone = $_POST['delivery-zone'] ?? 'Cairo';
        $paymentMethod = $_POST['payment-method'];

        // Get cloud kitchen ID
        $query = "SELECT m.cloud_kitchen_id FROM cart_items ci
                  JOIN meals m ON ci.meal_id = m.meal_id
                  WHERE ci.cart_id = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['cart_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $cloudKitchen = $result->fetch_assoc();
        $cloudKitchenId = $cloudKitchen['cloud_kitchen_id'];
        $stmt->close();

        // Insert order
        $insertOrderQuery = "INSERT INTO orders (customer_id, cloud_kitchen_id, total_price, ord_type, delivery_zone, order_status) 
                             VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($insertOrderQuery);
        $stmt->bind_param("iidss", $userId, $cloudKitchenId, $total, $orderType, $deliveryZone);

        if ($stmt->execute()) {
            $orderId = $stmt->insert_id;

            // Insert order items
            foreach ($cartItems as $item) {
                $insertOrderContentQuery = "INSERT INTO order_content (order_id, meal_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmtOC = $conn->prepare($insertOrderContentQuery);
                $stmtOC->bind_param("iiid", $orderId, $item['meal_id'], $item['quantity'], $item['price']);
                $stmtOC->execute();
                $stmtOC->close();
            }

            // Insert payment
            $totalPayment = $total;
            $websiteRevenue = 0;
            $insertPaymentQuery = "INSERT INTO payment_details (order_id, total_ord_price, delivery_fees, website_revenue, total_payment, p_date_time, p_method) 
                                   VALUES (?, ?, ?, ?, ?, NOW(), ?)";
            $stmtP = $conn->prepare($insertPaymentQuery);
            $stmtP->bind_param("iddiss", $orderId, $subtotal, $deliveryFees, $websiteRevenue, $totalPayment, $paymentMethod);
            $stmtP->execute();
            $stmtP->close();

            // Insert placeholder review row (stars set to 0 for now)
$placeholderStars = 0;
$insertReviewQuery = "INSERT INTO reviews (stars, order_id, cloud_kitchen_id, customer_id) 
                      VALUES (?, ?, ?, ?)";
$stmtR = $conn->prepare($insertReviewQuery);
$stmtR->bind_param("iiii", $placeholderStars, $orderId, $cloudKitchenId, $userId);
$stmtR->execute();
$stmtR->close();

// Increment orders_count for cloud kitchen owner
        $updateKitchenOwnerQuery = "UPDATE cloud_kitchen_owner SET orders_count = orders_count + 1 WHERE user_id = ?";
        $stmtUpdateOwner = $conn->prepare($updateKitchenOwnerQuery);
        $stmtUpdateOwner->bind_param("i", $cloudKitchenId);
        $stmtUpdateOwner->execute();
        $stmtUpdateOwner->close();


            // Delete cart items
            $deleteCartItemsQuery = "DELETE FROM cart_items WHERE cart_id = ?";
            $stmtDeleteItems = $conn->prepare($deleteCartItemsQuery);
            $stmtDeleteItems->bind_param("i", $_SESSION['cart_id']);
            $stmtDeleteItems->execute();
            $stmtDeleteItems->close();

            // Delete cart record
            $deleteCartQuery = "DELETE FROM cart WHERE cart_id = ?";
            $stmtDeleteCart = $conn->prepare($deleteCartQuery);
            $stmtDeleteCart->bind_param("i", $_SESSION['cart_id']);
            $stmtDeleteCart->execute();
            $stmtDeleteCart->close();

            // Clear session
            unset($_SESSION['cart_id']);

            $successMessage = "Order placed successfully! Your order ID is: " . $orderId;
        } else {
            $errors[] = "Failed to create order. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review & Pay - Food Delivery Checkout</title>
  <meta name="description" content="Review your order and complete payment for your food delivery.">
  <link rel="stylesheet" href="../global.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .hidden { display: none; }
    .summary-details { margin-top: 10px; border-top: 1px solid #ccc; padding-top: 10px; }
    .summary-item { display: flex; justify-content: space-between; padding: 5px 0; }
    .summary-item.total { font-weight: bold; font-size: 1.2em; border-top: 2px solid #2b0e0e80; margin-top: 25px; }
    .error-message { color: #f44336; background-color: #ffebee; padding: 15px; margin-bottom: 20px; border-radius: 4px; display: flex; align-items: center; }
    .error-message i { margin-right: 10px; }
    .success-message { color: #4CAF50; background-color: #e8f5e9; padding: 15px; margin-bottom: 20px; border-radius: 4px; display: flex; align-items: center; }
    .success-message i { margin-right: 10px; }
  </style>
</head>
<body>
  <?php include '..\..\global\navbar\navbar.php'; ?>

  <div class="container">
    <h1>Checkout</h1>
    
    <?php if (!empty($errors)): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo implode('<br>', $errors); ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($successMessage)): ?>
      <div class="success-message">
        <i class="fas fa-check-circle"></i>
        <?php echo $successMessage; ?>
      </div>
    <?php endif; ?>
    
    <div class="checkout-steps">
      <div class="step">
        <div class="step-circle completed">
          <img src="../icons/check.svg" alt="Completed" width="16" height="16">
        </div>
        <span class="step-name">Order Type</span>
      </div>
      
      <div class="step">
        <div class="step-circle completed">
          <img src="../icons/check.svg" alt="Completed" width="16" height="16">
        </div>
        <span class="step-name">Meal Planning</span>
      </div>
      
      <div class="step">
        <div class="step-circle active">
          <span>3</span>
        </div>
        <span class="step-name">Review & Pay</span>
      </div>
    </div>
    
    <form method="POST" action="">
      <div class="main-content">
        <div class="main-column">
          <div class="card">
            <div class="card-content">
              <h2 class="card-title">Delivery Details</h2>
              
              <div class="grid-container">
                <div class="field-grid">
                  <div class="form-p-group">
                    <label class="form-label" for="first-name">
                      <i class="fas fa-user"></i> First Name
                    </label>
                    <input type="text" id="first-name" name="first-name" class="form-input" value="<?php echo htmlspecialchars($firstName); ?>" readonly>
                  </div>

                  <div class="form-p-group">
                    <label class="form-label" for="email">
                      <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email); ?>" readonly>
                  </div>

                  <div class="form-p-group">
                    <label class="form-label" for="phone">
                      <i class="fas fa-phone"></i> Phone Number
                    </label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($phone); ?>" placeholder="+1 234 567 8900" required>
                  </div>
                </div>
                
                <div class="form-p-group">
                  <label class="form-label" for="address">
                    <i class="fas fa-map-marker-alt"></i> Delivery Address
                  </label>
                  <textarea id="address" name="address" class="form-textarea" rows="3" placeholder="123 Main St, Apt 4B, New York, 10001" required><?php echo htmlspecialchars($address); ?></textarea>
                </div>
              </div>
              
              <h2 class="card-title" style="margin-top: 2rem;">Payment Method</h2>
              
              <div class="payment-methods">
                <div class="payment-method">
                  <input type="radio" id="cash-on-delivery" name="payment-method" value="cash" class="radio-input" checked>
                  <label for="cash-on-delivery" class="payment-method-label">
                    <span class="payment-method-icon">
                      <img src="../icons/cash.svg" alt="Cash" width="24" height="24">
                    </span>
                    Cash on Delivery
                  </label>
                </div>

                <div class="payment-method">
                  <input type="radio" id="card-payment" name="payment-method" value="card" class="radio-input">
                  <label for="card-payment" class="payment-method-label">
                    <span class="payment-method-icon">
                      <img src="../icons/credit-card.svg" alt="Credit Card" width="24" height="24">
                    </span>
                    Credit/Debit Card
                    <div class="payment-cards">
                      <img src="https://freebiehive.com/wp-content/uploads/2024/05/Visa-Logo-PNG-1.jpg" alt="Visa" class="payment-card-icon">
                      <img src="../icons/mastercard.svg" alt="Mastercard" class="payment-card-icon">
                    </div>
                  </label>
                </div>
              </div>
              
              <div class="button-container">
                <a href="/NEW-TODAYS-MEAL/customer/cart/cart.php" class="button button-outline">Back to Cart</a>
                <button type="submit" name="place-order" id="place-order-button" class="button button-primary">Place Order</button>
              </div>
            </div>
          </div>
        </div>

        <div class="sidebar">
          <div class="card order-summary" id="order-summary">
            <div class="card-content">
              <h2 class="order-summary-title">Order Summary</h2>
              <ul style="list-style: none; padding-left: 0;">
                <?php
                $imagePath = '../../../uploads/meals/';
                foreach ($cartItems as $item) {
                    $mealImage = htmlspecialchars($imagePath . $item['photo']);
                    $mealName = htmlspecialchars($item['meal_name']);
                    $quantity = (int)$item['quantity'];
                    $price = number_format($item['price'], 2);

                    echo "<li style='display: flex; align-items: flex-start; gap: 10px; margin-bottom: 15px;'>";
                    echo "<img src='{$mealImage}' alt='{$mealName}' style='width: 50px; height: 50px; object-fit: cover; border-radius: 4px;'>";
                    echo "<div style='flex: 1;'>";
                    echo "<div style='display: flex; justify-content: space-between; font-weight: bold;'>";
                    echo "<span>{$mealName}</span>";
                    echo "<span>EGP{$price}</span>";
                    echo "</div>";
                    echo "<div style='font-size: 0.85em; color: #555;'>Quantity: {$quantity}</div>";
                    echo "</div>";
                    echo "</li>";
                }
                ?>
              </ul>
              <div class="summary-details">
                <div class="summary-item">
                  <span>Subtotal:</span>
                  <span>EGP<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-item">
                  <span>Delivery Fees:</span>
                  <span>EGP<?php echo number_format($deliveryFees, 2); ?></span>
                </div>
                <div class="summary-item total">
                  <span>Total:</span>
                  <span>EGP<?php echo number_format($total, 2); ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <?php include '..\..\global\footer\footer.php'; ?>
</body>
</html>