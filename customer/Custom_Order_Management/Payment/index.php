<?php
// place_order.php

session_start();
include "../../DB_connection.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit();
}

// Fetch order and user data (GET)
$order = [];
$user = [];
$errors = [];
$subtotal = 0;
$taxAmount = 0;
$deliveryFee = 0;
$websiteRevenue = 0;
$total = 0;

if (isset($_GET['order_id'])) {
    $orderId = intval($_GET['order_id']);

    // Get chosen_amount from customized_order + order info
    $stmt = $conn->prepare("
        SELECT co.chosen_amount, co.ord_description, o.order_id
        FROM orders o
        JOIN customized_order co ON o.order_id = co.order_id
        WHERE o.order_id = ? AND o.customer_id = ?
    ");
    $stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
    $stmt->execute();
    $orderResult = $stmt->get_result();

    if ($orderResult->num_rows > 0) {
        $order = $orderResult->fetch_assoc();
        $subtotal = floatval($order['chosen_amount']);  // Use chosen_amount here!
        if ($subtotal <= 0) {
            $errors[] = "Chosen budget amount is zero or not set.";
        }
        $taxAmount = $subtotal * 0.07;
        $deliveryFee = 20.00;
        $websiteRevenue = $subtotal * 0.05;
        $total = $subtotal + $taxAmount + $deliveryFee + $websiteRevenue;
    } else {
        $errors[] = "Order not found or does not belong to you.";
    }
    $stmt->close();

    // Get user info
    $stmt = $conn->prepare("SELECT u.u_name, u.mail, u.phone, eu.address 
                            FROM users u 
                            JOIN external_user eu ON u.user_id = eu.user_id 
                            WHERE u.user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $userResult = $stmt->get_result();
    if ($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
    }
    $stmt->close();
}

// Handle POST (pure form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $paymentMethod = $_POST['payment_method'];
    $paymentStatus = ($paymentMethod === 'cash') ? 'pending' : 'paid';

    // Recalculate amounts fresh from customized_order on POST:
    $stmt = $conn->prepare("
        SELECT chosen_amount 
        FROM customized_order 
        WHERE order_id = ? AND customer_id = ?
    ");
    $stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $subtotal = floatval($row['chosen_amount']);
        if ($subtotal <= 0) {
            $errors[] = "Chosen budget amount is zero or not set.";
        }
        $taxAmount = $subtotal * 0.07;
        $deliveryFee = 20.00;
        $websiteRevenue = $subtotal * 0.05;
        $total = $subtotal + $taxAmount + $deliveryFee + $websiteRevenue;
    } else {
        $errors[] = "Order not found.";
    }
    $stmt->close();

    if (empty($errors)) {
        try {
            $conn->begin_transaction();

            // 1. Approve customized order
            $stmt = $conn->prepare("UPDATE customized_order 
                                    SET customer_approval = 'approved' 
                                    WHERE order_id = ? AND customer_id = ?");
            $stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            // 2. Update order info (delivery zone)
            $stmt = $conn->prepare("UPDATE orders 
                                    SET delivery_zone = ? 
                                    WHERE order_id = ? AND customer_id = ?");
            $stmt->bind_param("sii", $address, $orderId, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            // 3. Insert payment details
            $stmt = $conn->prepare("INSERT INTO payment_details (
                order_id, total_ord_price, delivery_fees, website_revenue, total_payment,
                p_date_time, p_method, payment_status
            ) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
            $stmt->bind_param("iddddss", $orderId, $subtotal, $deliveryFee, $websiteRevenue, $total, $paymentMethod, $paymentStatus);
            $stmt->execute();
            $stmt->close();

            // 4. Update contact info
            $stmt = $conn->prepare("UPDATE users u JOIN external_user eu ON u.user_id = eu.user_id 
                                    SET u.phone = ?, eu.address = ? 
                                    WHERE u.user_id = ?");
            $stmt->bind_param("ssi", $phone, $address, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            header("Location: ../index.php?order_id=$orderId");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Failed to place order: " . $e->getMessage();
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
    <meta name="description" content="Review your order and complete payment for your food delivery." />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
<?php include '../../global/navbar/navbar.php'; ?>

<!-- Checkout Header -->
<div class="checkout-header">
    <h1>Checkout</h1>
</div>

<div class="container">
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo implode('<br>', $errors); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($order) && !empty($user)): ?>
    <form method="post" action="">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>" />

        <div class="checkout-content">
            <div class="left-column">
                <div class="card">
                    <h2>Delivery Details</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first-name">First Name</label>
                            <input type="text" id="first-name" value="<?= htmlspecialchars($user['u_name']) ?>" readonly />
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?= htmlspecialchars($user['mail']) ?>" readonly />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>" required />
                    </div>

                    <div class="form-group">
                        <label for="address">Delivery Address</label>
                        <textarea name="address" id="address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                </div>

                <div class="card">
                    <h2>Payment Method</h2>
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cash" checked />
                            <div class="payment-content">
                                <img src="cash.svg" alt="Cash Icon" class="payment-icon-img" />
                                <span>Cash on Delivery</span>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="visa" />
                            <div class="payment-content">
                                <img src="credit-card.svg" alt="Card Icon" class="payment-icon-img" />
                                <span>Credit/Debit Card</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="right-column">
                <div class="card">
                    <h2>Order Summary</h2>
                    <div class="order-name">
                        <h3><?= htmlspecialchars($order['ord_description']) ?></h3>
                    </div>

                    <div class="order-invoice">
                        <h4><i class="fas fa-receipt"></i> Order Invoice</h4>

                        <div class="invoice-details">
                            <div class="invoice-row"><span>Custom Order Price:</span><span>EGP <?= number_format($subtotal, 2) ?></span></div>
                            <div class="invoice-row"><span>Tax (7%):</span><span>EGP <?= number_format($taxAmount, 2) ?></span></div>
                            <div class="invoice-row"><span>Delivery Fee:</span><span>EGP <?= number_format($deliveryFee, 2) ?></span></div>
                            <div class="invoice-row"><span>Platform Fee:</span><span>EGP <?= number_format($websiteRevenue, 2) ?></span></div>
                            <hr />
                            <div class="invoice-total"><span>Total Amount:</span><span>EGP <?= number_format($total, 2) ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button type="button" class="back" onclick="history.back();">Back</button>
            <button type="submit" class="place-order-btn">Place Order</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php include '../../global/footer/footer.php'; ?>
</body>
</html>
