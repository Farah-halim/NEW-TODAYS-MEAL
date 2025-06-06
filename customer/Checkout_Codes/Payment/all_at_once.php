<?php
session_start();
require_once('../../DB_connection.php');
$errors = [];
$successMessage = '';

if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

if (!isset($_SESSION['cart_id']) || !is_numeric($_SESSION['cart_id'])) {
    $stmtGetCart = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1");
    if ($stmtGetCart) {
        $stmtGetCart->bind_param("i", $userId);
        $stmtGetCart->execute();
        $resultCart = $stmtGetCart->get_result();
        if ($resultCart && $resultCart->num_rows > 0) {
            $rowCart = $resultCart->fetch_assoc();
            $_SESSION['cart_id'] = (int)$rowCart['cart_id'];
        } else {
            $_SESSION['redirect_reason'] = "Your cart is empty or expired.";
            header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
            exit();
        }
        $stmtGetCart->close();
    } else {
        $_SESSION['redirect_reason'] = "Unexpected database error. Please try again.";
        header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
        exit();
    }
}

$cartId = (int)$_SESSION['cart_id'];

$stmtCartCheck = $conn->prepare("SELECT 1 FROM cart WHERE cart_id = ? AND customer_id = ?");
if ($stmtCartCheck) {
    $stmtCartCheck->bind_param("ii", $cartId, $userId);
    $stmtCartCheck->execute();
    $stmtCartCheck->store_result();
    if ($stmtCartCheck->num_rows === 0) {
        $stmtCartCheck->close();
        unset($_SESSION['cart_id']); // Clear invalid cart session
        $_SESSION['redirect_reason'] = "Cart session expired or invalid. Please add items to your cart.";
        header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
        exit();
    }
    $stmtCartCheck->close();
} else {
    $_SESSION['redirect_reason'] = "Unexpected error. Please try again.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}

if (isset($_POST['save-details'])) {
    $newPhone = trim($_POST['phone'] ?? '');
    $newAddress = trim($_POST['address'] ?? '');

    if ($newPhone === '') {
        $errors[] = "Phone number is required";
    }

    if ($newAddress === '') {
        $errors[] = "Address is required";
    }

    if (empty($errors)) {
        $updatePhoneQuery = "UPDATE users SET phone = ? WHERE user_id = ?";
        $stmtPhone = $conn->prepare($updatePhoneQuery);
        if (!$stmtPhone) {
            $errors[] = "Database error updating phone.";
        } else {
            $stmtPhone->bind_param("si", $newPhone, $userId);
            $stmtPhone->execute();
            $stmtPhone->close();
        }

        $updateAddressQuery = "UPDATE external_user SET address = ? WHERE user_id = ?";
        $stmtAddress = $conn->prepare($updateAddressQuery);
        if (!$stmtAddress) {
            $errors[] = "Database error updating address.";
        } else {
            $stmtAddress->bind_param("si", $newAddress, $userId);
            $stmtAddress->execute();
            $stmtAddress->close();
        }

        if (empty($errors)) {
            $successMessage = "Your details have been updated successfully!";
            $phone = $newPhone;
            $address = $newAddress;
        }
    }
}

$firstName = $email = $phone = $address = '';
$isSubscribed = false;

// Get user details safely
$query = "SELECT u.u_name, u.mail, u.phone, eu.address, c.is_subscribed
          FROM users u
          JOIN external_user eu ON u.user_id = eu.user_id
          LEFT JOIN customer c ON eu.user_id = c.user_id
          WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $errors[] = "Database error retrieving user data.";
} else {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $firstName = $user['u_name'];
        $email = $user['mail'];
        $phone = $user['phone'];
        $address = $user['address'];
        $isSubscribed = (bool)$user['is_subscribed'];
    } else {
        $errors[] = "User not found or not a customer.";
    }

    $stmt->close();
}

$cartItems = [];

$query = "SELECT ci.*, m.name as meal_name, m.price, m.photo 
          FROM cart_items ci
          JOIN meals m ON ci.meal_id = m.meal_id
          WHERE ci.cart_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $errors[] = "Database error retrieving cart items.";
} else {
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($cartItems)) {
        unset($_SESSION['cart_id']); 
        $_SESSION['redirect_reason'] = "Your cart is empty.";
        header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
        exit();
    }
}

$subtotal = 0.0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$deliveryFees = 15.00;
if ($isSubscribed) {
    $query = "SELECT 1 FROM delivery_subscriptions 
              WHERE customer_id = ? AND is_active = 1 AND end_date >= CURDATE()";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $deliveryFees = 0.00;
        }
        $stmt->close();
    }
}

$total = $subtotal + $deliveryFees;

$deliveryType = 'all_at_once';
$deliveryDate = $_SESSION['order_data']['deliveryDay'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place-order'])) {
    $orderType = 'scheduled';
    $deliveryZone = $_POST['delivery-zone'] ?? 'Cairo';
    $paymentMethod = $_POST['payment-method'] ?? '';

    if (!in_array($paymentMethod, ['cash', 'card'], true)) {
        $errors[] = "Invalid payment method.";
    }

    $query = "SELECT m.cloud_kitchen_id FROM cart_items ci
              JOIN meals m ON ci.meal_id = m.meal_id
              WHERE ci.cart_id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $errors[] = "Database error retrieving cloud kitchen information.";
    } else {
        $stmt->bind_param("i", $cartId);
        $stmt->execute();
        $result = $stmt->get_result();
        $cloudKitchen = $result->fetch_assoc();

        if ($cloudKitchen) {
            $cloudKitchenId = (int)$cloudKitchen['cloud_kitchen_id'];
        } else {
            $errors[] = "Failed to retrieve cloud kitchen information.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $insertOrderQuery = "INSERT INTO orders 
            (customer_id, cloud_kitchen_id, total_price, ord_type, delivery_type, customer_selected_date, delivery_zone) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertOrderQuery);
        if (!$stmt) {
            $errors[] = "Database error creating order.";
        } else {
            $stmt->bind_param(
                "iiissss",
                $userId,
                $cloudKitchenId,
                $total,
                $orderType,
                $deliveryType,
                $deliveryDate,
                $deliveryZone
            );
            if ($stmt->execute()) {
                $orderId = $stmt->insert_id;

                $insertOrderContentQuery = "INSERT INTO order_content (order_id, meal_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmtOC = $conn->prepare($insertOrderContentQuery);
                if (!$stmtOC) {
                    $errors[] = "Database error inserting order content.";
                } else {
                    foreach ($cartItems as $item) {
                        $stmtOC->bind_param("iiid", $orderId, $item['meal_id'], $item['quantity'], $item['price']);
                        $stmtOC->execute();
                    }
                    $stmtOC->close();
                }

                $websiteRevenue = 0.00;
                $insertPaymentQuery = "INSERT INTO payment_details 
                    (order_id, total_ord_price, delivery_fees, website_revenue, total_payment, p_date_time, p_method) 
                    VALUES (?, ?, ?, ?, ?, NOW(), ?)";
                $stmtP = $conn->prepare($insertPaymentQuery);
                if (!$stmtP) {
                    $errors[] = "Database error inserting payment details.";
                } else {
                    $stmtP->bind_param("iddiss", $orderId, $subtotal, $deliveryFees, $websiteRevenue, $total, $paymentMethod);
                    $stmtP->execute();
                    $stmtP->close();
                }

                $placeholderStars = 0;
                $insertReviewQuery = "INSERT INTO reviews (stars, order_id, cloud_kitchen_id, customer_id) VALUES (?, ?, ?, ?)";
                $stmtR = $conn->prepare($insertReviewQuery);
                if (!$stmtR) {
                    $errors[] = "Database error inserting review.";
                } else {
                    $stmtR->bind_param("iiii", $placeholderStars, $orderId, $cloudKitchenId, $userId);
                    $stmtR->execute();
                    $stmtR->close();
                }

                $updateKitchenOwnerQuery = "UPDATE cloud_kitchen_owner SET orders_count = orders_count + 1 WHERE user_id = ?";
                $stmtUpdateOwner = $conn->prepare($updateKitchenOwnerQuery);
                if (!$stmtUpdateOwner) {
                    $errors[] = "Database error updating cloud kitchen owner.";
                } else {
                    $stmtUpdateOwner->bind_param("i", $cloudKitchenId);
                    $stmtUpdateOwner->execute();
                    $stmtUpdateOwner->close();
                }

                $deleteCartItemsQuery = "DELETE FROM cart_items WHERE cart_id = ?";
                $stmtDeleteItems = $conn->prepare($deleteCartItemsQuery);
                if ($stmtDeleteItems) {
                    $stmtDeleteItems->bind_param("i", $cartId);
                    $stmtDeleteItems->execute();
                    $stmtDeleteItems->close();
                }

                $deleteCartQuery = "DELETE FROM cart WHERE cart_id = ?";
                $stmtDeleteCart = $conn->prepare($deleteCartQuery);
                if ($stmtDeleteCart) {
                    $stmtDeleteCart->bind_param("i", $cartId);
                    $stmtDeleteCart->execute();
                    $stmtDeleteCart->close();
                }
                unset($_SESSION['cart_id']);
                unset($_SESSION['order_data']);

                $_SESSION['order_success'] = true;
                $_SESSION['order_id'] = $orderId;

                header("Location: /NEW-TODAYS-MEAL/customer/Cart/cart.php");
                exit();
            } else {
                $errors[] = "Failed to create order. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review & Pay - Food Delivery Checkout</title>
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
      <div class="step completed">
        <div class="step-circle completed">
          <img src="../icons/check.svg" alt="Completed" width="16" height="16">
        </div>
        <span class="step-name">Order Type</span>
      </div>
      
      <div class="step completed">
        <div class="step-circle completed">
          <img src="../icons/check.svg" alt="Completed" width="16" height="16">
        </div>
        <span class="step-name">Meal Planning</span>
      </div>
      
      <div class="step active">
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
                
                <button type="submit" name="save-details" class="button button-outline" style="margin-top: 10px;">
                  <i class="fas fa-save" style="margin-right:5px"></i> Save Changes
                </button>
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
              <h2 class="order-summary-title"> Order Summary</h2>
              <ul style="list-style: none; padding-left: 0;">
                <?php
                $imagePath = '../../../../uploads/meals/';
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
                } ?>
              </ul>
              <div class="summary-details">
                <div class="summary-item">
                  <span><i class="fas fa-truck"></i> Delivery Type:</span>
                  <span><?php echo htmlspecialchars($deliveryType === 'all_at_once' ? 'All at Once' : 'Daily Delivery'); ?></span>
                </div>
                <div class="summary-item">
                  <span><i class="far fa-calendar-alt"></i> Delivery Date:</span>
                  <span><?php echo htmlspecialchars(date('l, F j, Y', strtotime($deliveryDate))); ?></span>
                </div>
                <div class="summary-item">
                  <span><i class="fas fa-shopping-basket"></i>  Subtotal:</span>
                  <span>EGP<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-item">
                  <span>  <i class="fas fa-tag"></i>  Delivery Fees:</span>
                  <span>EGP<?php echo number_format($deliveryFees, 2); ?></span>
                </div>
                <div class="summary-item total">
                  <span><i class="fas fa-wallet"></i> Total:</span>
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