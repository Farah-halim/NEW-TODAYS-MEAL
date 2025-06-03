<?php
session_start();
require_once('../../DB_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if (!isset($_SESSION['cart_id'])) {
    $_SESSION['redirect_reason'] = "Cart session expired.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}

$cartId = $_SESSION['cart_id'];

$stmt = $conn->prepare("SELECT COUNT(*) AS item_count FROM cart_items WHERE cart_id = ?");
$stmt->bind_param("i", $cartId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row['item_count'] == 0) {
    $_SESSION['redirect_reason'] = "Your cart is empty.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}

$errors = [];
$firstName = $email = $phone = $address = "";
$isSubscribed = false;
$cartItems = [];
$subtotal = 0;
$deliveryFee = 15.00;

// Get user details and subscription status
$stmt = $conn->prepare("
    SELECT u.u_name, u.mail, u.phone, eu.address, c.is_subscribed
    FROM users u
    JOIN external_user eu ON u.user_id = eu.user_id
    LEFT JOIN customer c ON eu.user_id = c.user_id
    WHERE u.user_id = ?");
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
}
$stmt->close();

// Fetch cart items & calculate subtotal
$stmt = $conn->prepare("
    SELECT ci.*, m.name AS meal_name, m.price, m.photo 
    FROM cart_items ci 
    JOIN meals m ON ci.meal_id = m.meal_id 
    WHERE ci.cart_id = ?");
$stmt->bind_param("i", $cartId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $subtotal += $row['price'] * $row['quantity'];
}
$stmt->close();

// Check subscription to waive delivery fee
if ($isSubscribed) {
    $stmt = $conn->prepare("
        SELECT 1
        FROM delivery_subscriptions
        WHERE customer_id = ? AND is_active = 1 AND end_date >= CURDATE()
        LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $subResult = $stmt->get_result();
    if ($subResult->num_rows > 0) {
        $deliveryFee = 0.00;
    }
    $stmt->close();
}

$total = $subtotal + $deliveryFee;

// Build order summary from scheduled meals in session grouped by delivery date
$orderPackages = [];
if (!empty($_SESSION['order_data']['scheduledMeals'])) {
    foreach ($_SESSION['order_data']['scheduledMeals'] as $scheduledMeal) {
        // The delivery date for this meal is the date chosen by customer - use exactly that
        $day = $scheduledMeal['day']; // expected 'YYYY-MM-DD' format (customer selected delivery date)
        $mealId = $scheduledMeal['meal_id'];

        foreach ($cartItems as $ci) {
            if ($ci['meal_id'] == $mealId) {
                if (!isset($orderPackages[$day])) {
                    $orderPackages[$day] = [
                        'date' => $day, // store customer's chosen date here precisely
                        'meals' => [],
                        'day_total' => 0.0
                    ];
                }
                // Add this meal to the package for that date
                $orderPackages[$day]['meals'][] = [
                    'meal_id' => $ci['meal_id'],
                    'meal_name' => $ci['meal_name'],
                    'meal_image' => $ci['photo'],
                    'quantity' => 1,
                    'price' => $ci['price']
                ];
                $orderPackages[$day]['day_total'] += $ci['price'];
                break;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place-order'])) {
    $paymentMethod = $_POST['payment-method'] ?? '';
    $deliveryZone = $_POST['delivery-zone'] ?? 'Cairo';

    if (!in_array($paymentMethod, ['cash', 'visa', 'card'])) {
        $errors[] = "Invalid payment method selected.";
    }

    if (empty($orderPackages)) {
        $errors[] = "No scheduled meals found to place order.";
    }

    if (empty($errors)) {
        // Get cloud kitchen ID (assuming all meals belong to same kitchen)
        $stmt = $conn->prepare("
            SELECT m.cloud_kitchen_id 
            FROM cart_items ci 
            JOIN meals m ON ci.meal_id = m.meal_id 
            WHERE ci.cart_id = ? LIMIT 1");
        $stmt->bind_param("i", $cartId);
        $stmt->execute();
        $result = $stmt->get_result();
        $cloudKitchenRow = $result->fetch_assoc();
        $stmt->close();

        if (!$cloudKitchenRow) {
            $errors[] = "Could not find cloud kitchen info.";
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            // Insert order with ord_type='scheduled' and delivery_type='daily_delivery'
            $stmt = $conn->prepare("
                INSERT INTO orders 
                    (customer_id, cloud_kitchen_id, total_price, ord_type, delivery_type, delivery_zone) 
                VALUES (?, ?, ?, 'scheduled', 'daily_delivery', ?)");
            $stmt->bind_param("iids", $userId, $cloudKitchenRow['cloud_kitchen_id'], $total, $deliveryZone);
            $stmt->execute();
            $orderId = $conn->insert_id;
            $stmt->close();

            $cloudKitchenId = $cloudKitchenRow['cloud_kitchen_id']; 

$updateKitchenOwnerQuery = "UPDATE cloud_kitchen_owner SET orders_count = orders_count + 1 WHERE user_id = ?";
$stmtUpdateOwner = $conn->prepare($updateKitchenOwnerQuery);
$stmtUpdateOwner->bind_param("i", $cloudKitchenId);
$stmtUpdateOwner->execute();
$stmtUpdateOwner->close();


            // Insert order_packages using the exact delivery_date the customer selected!
            $packageCounter = 1;
            foreach ($orderPackages as $day => $package) {
                $packageName = "Package (" . $packageCounter++ . ")";
                $deliveryDate = $package['date']; // <--- customer chosen date directly inserted here

                $stmt = $conn->prepare("
                    INSERT INTO order_packages 
                        (order_id, package_name, delivery_date, package_price, payment_status, package_status) 
                    VALUES (?, ?, ?, ?, 'pending', 'pending')");
                $stmt->bind_param("issd", $orderId, $packageName, $deliveryDate, $package['day_total']);
                $stmt->execute();
                $packageId = $conn->insert_id;
                $stmt->close();

                // Insert meals in this package
                foreach ($package['meals'] as $meal) {
                    $stmtMeal = $conn->prepare("
                        INSERT INTO meals_in_each_package 
                            (package_id, meal_id, quantity, price) 
                        VALUES (?, ?, ?, ?)");
                    $stmtMeal->bind_param("iiid", $packageId, $meal['meal_id'], $meal['quantity'], $meal['price']);
                    $stmtMeal->execute();
                    $stmtMeal->close();
                }
            }

            // Insert payment details with current timestamp for p_date_time (even if status is pending)
            $websiteRevenue = round($total * 0.10, 2); // 10% commission example
            $paymentStatus = 'pending';
            $currentDateTime = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("
                INSERT INTO payment_details 
                    (order_id, total_ord_price, delivery_fees, website_revenue, total_payment, p_date_time, p_method, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idddssss", $orderId, $subtotal, $deliveryFee, $websiteRevenue, $total, $currentDateTime, $paymentMethod, $paymentStatus);
            $stmt->execute();
            $stmt->close();

            // Clear cart items and cart
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            $stmt->bind_param("i", $cartId);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ?");
            $stmt->bind_param("i", $cartId);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            unset($_SESSION['cart_id'], $_SESSION['order_data']);

            $_SESSION['order_success'] = true;
            $_SESSION['order_id'] = $orderId;

            header("Location: ..\..\Cart\cart.php");
            exit();
        } catch (Exception $ex) {
            $conn->rollback();
            $errors[] = "Order placement failed: " . $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Review & Pay - Food Delivery Checkout</title>
<link rel="stylesheet" href="../global.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>


.error-message {
    border: 1px solid var(--destructive);
    background: #ffebe8;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: var(--radius);
    color: var(--destructive);
}

/* Order Summary Styling */
.order-summary-card {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    padding: 24px;
    border: 1px solid var(--border);
}



.package-card {
    background: hsl(34deg 100% 85.17% / 34%);
    border-radius: var(--radius);
    padding: 16px;
    margin-bottom: 20px;
    border-left: 4px solid var(--primary);
}

.package-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.package-date {
    font-weight: 600;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.package-meal-count {
    background: rgba(142, 64, 22, 0.1);
    color: var(--primary);
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 500;
}

.meal-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}

.meal-item:last-child {
    border-bottom: none;
}

.meal-image {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 12px;
    border: 1px solid var(--border);
}

.meal-details {
    flex: 1;
}

.meal-name {
    font-weight: 500;
}



.price-summary {
    margin-top: 20px;
    border-top: 1px solid var(--border);
    padding-top: 16px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}


.price-value {
    font-weight: 500;
}

.total-row {
    border-top: 1px dashed var(--border);
    padding-top: 12px;
    margin-top: 12px;
    font-size: 1.1rem;
}

.total-label {
    font-weight: 600;
}

.total-value {
    font-weight: 700;
    font-size: 1.2rem;
}

/* Form Styling */
.card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}



/* Responsive adjustments */
@media (max-width: 768px) {
    .main-content {
        flex-direction: column;
    }
    .sidebar {
        margin-top: 20px;
    }
}
</style>
</head>
<body>
<?php include '../../global/navbar/navbar.php'; ?>
<div class="container">
<h1>Checkout</h1>

<?php if (!empty($errors)): ?>
    <div class="error-message" role="alert">
        <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
        <?= implode("<br>", $errors); ?>
    </div>
<?php endif; ?>

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
            <div class="order-summary-card">
                <h2 class="order-summary-title"> Order Summary
                </h2>
                
                <?php if (!empty($orderPackages)): ?>
                    <?php $packageIndex = 1; ?>
                    <?php foreach ($orderPackages as $day => $package): ?>
                        <div class="package-card">
                            <div class="package-header">
                                <div class="package-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('l, M j, Y', strtotime($day)) ?>
                                </div>
                                <span class="package-meal-count">
                                    <?= count($package['meals']) ?> meal<?= count($package['meals']) > 1 ? 's' : '' ?>
                                </span>
                            </div>
                            
                            <div class="package-meals">
                                <?php foreach ($package['meals'] as $meal): ?>
                                    <div class="meal-item">
                                        <img src="<?= htmlspecialchars($meal['meal_image']) ?>" alt="<?= htmlspecialchars($meal['meal_name']) ?>" class="meal-image">
                                        <div class="meal-details">
                                            <div class="meal-name"><?= htmlspecialchars($meal['meal_name']) ?></div>
                                        </div>
                                        <div class="meal-price">
                                            <?= number_format($meal['price'], 2) ?> EGP
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="price-row" style="margin-top: 10px;">
                                <span class="price-label">
                                    <i class="fas fa-tag"></i> Package Total:
                                </span>
                                <span class="price-value">
                                    <?= number_format($package['day_total'], 2) ?> EGP
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No scheduled meals found.</p>
                <?php endif; ?>
                
                <div class="price-summary">
                    <div class="price-row">
                        <span class="price-label">
                            <i class="fas fa-shopping-basket"></i> Subtotal:
                        </span>
                        <span class="price-value">
                            <?= number_format($subtotal, 2) ?> EGP
                        </span>
                    </div>
                    
                    <div class="price-row">
                        <span class="price-label">
                            <i class="fas fa-truck"></i> Delivery Fee:
                        </span>
                        <span class="price-value">
                            <?= number_format($deliveryFee, 2) ?> EGP
                        </span>
                    </div>
                    
                    <div class="price-row total-row">
                        <span class="total-label">
                            <i class="fas fa-wallet"></i> Total:
                        </span>
                        <span class="total-value">
                            <?= number_format($total, 2) ?> EGP
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
</div>

<?php include '../../global/footer/footer.php'; ?>
</body>
</html>