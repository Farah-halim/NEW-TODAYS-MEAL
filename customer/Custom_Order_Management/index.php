<?php
include "../DB_connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: \NEW-TODAYS-MEAL\Register&Login\login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

$orders = [];
if ($conn) {
    // Get ALL customized orders (regardless of payment status)
    $sql = "SELECT co.*, o.order_date, o.delivery_date, o.delivery_zone, 
                   o.order_status, 
                   cko.business_name AS cloud_kitchen_name,
                   u.u_name AS customer_name
            FROM customized_order co
            JOIN orders o ON co.order_id = o.order_id
            JOIN cloud_kitchen_owner cko ON co.kitchen_id = cko.user_id
            JOIN users u ON co.customer_id = u.user_id
            WHERE co.customer_id = ?
            ORDER BY o.order_date DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $customer_id);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Check if order exists in payment table
        $payment_sql = "SELECT payment_status FROM payment_details 
                       WHERE order_id = ?";
        $payment_stmt = $conn->prepare($payment_sql);
        if (!$payment_stmt) {
            die("Error preparing payment statement: " . $conn->error);
        }
        
        $payment_stmt->bind_param("i", $row['order_id']);
        if (!$payment_stmt->execute()) {
            die("Error executing payment statement: " . $payment_stmt->error);
        }
        
        $payment_result = $payment_stmt->get_result();
        
        if ($payment_result->num_rows > 0) {
            $payment_data = $payment_result->fetch_assoc();
            $row['payment_exists'] = true;
            $row['payment_status'] = $payment_data['payment_status'];
        } else {
            $row['payment_exists'] = false;
            $row['payment_status'] = null;
            $row['order_status'] = null; // Hide order_status if no payment
        }
        $payment_stmt->close();
        
        // Status mapping for customized orders
        if ($row['customer_approval'] == 'approved') {
            $row['status'] = 'price_accepted';
        } elseif ($row['customer_approval'] == 'rejected') {
            $row['status'] = 'rejected';
        } elseif ($row['status'] == 'rejected') {  
            $row['status'] = 'rejected';  
        } elseif ($row['status'] == 'accepted') {
            $row['status'] = 'approved';  
        } else {
            $row['status'] = 'pending';
        }
        
        $orders[] = $row;
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Catering Orders</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .order-header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .order-status-badge {
               padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #555;
    text-transform: capitalize;
        }

        .order-status-badge[data-status="pending"] {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
        }

        .order-status-badge[data-status="preparing"] {
            background-color: #cce5ff;
            color: #004085;
            border-color: #b8daff;
        }

        .order-status-badge[data-status="ready_for_pickup"] {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .order-status-badge[data-status="in_transit"] {
            background-color: #e2e3e5;
            color: #383d41;
            border-color: #d6d8db;
        }

        .order-status-badge[data-status="delivered"] {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        .order-status-badge[data-status="cancelled"] {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .order-status-badge[data-status="payment_processing"] {
            background-color: #e2d4f0;
            color: #4a2a7a;
            border-color: #d0bae4;
        }
    </style>
</head>
<body>
    <?php include '..\global\navbar\navbar.php'; ?>

    <div class="container">
        <div class="search-filter-section">
            <div class="search-bar">
                <div class="search-input-wrapper">
                    <img src="https://img.icons8.com/?size=100&id=3159&format=png&color=000000" alt="Search" class="search-icon">
                    <input type="text" id="searchInput" placeholder="Search your orders..." class="search-input">
                </div>
            </div>

            <div class="filter-controls">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All Orders</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="approved">Approved</button>
                    <button class="filter-btn" data-filter="rejected">Rejected</button>
                    <button class="filter-btn" data-filter="price_accepted">Price Accepted</button>
                </div>
            </div>
        </div>
        <div class="orders-list" id="ordersList">
            <?php foreach ($orders as $index => $order): 
                $order_number = str_pad($order['order_id'], 3, '0', STR_PAD_LEFT);
                $delivery_date = date('Y-m-d', strtotime($order['delivery_date']));
                $delivery_time = date('g:i A', strtotime($order['delivery_date']));
                $order_date = date('Y-m-d', strtotime($order['order_date']));
                
                $badge_class = '';
                $status_message = '';
                switch ($order['status']) {
                    case 'pending':
                        $badge_class = 'badge-pending';
                        $status_message = 'Your custom order is being reviewed';
                        break;
                    case 'approved':
                        $badge_class = 'badge-approved';
                        $status_message = 'Price quote ready for your review';
                        break;
                    case 'rejected':
                        $badge_class = 'badge-rejected';
                        $status_message = 'Order cancelled';
                        break;
                    case 'price_accepted':
                        $badge_class = 'badge-price-accepted';
                        $status_message = $order['payment_exists'] ? 'Payment completed' : 'Price accepted - Ready for checkout';
                        break;
                }
                
                $show_price_quote = ($order['status'] == 'approved' && $order['chosen_amount'] !== null);
                $show_invoice = ($order['status'] == 'price_accepted' && $order['chosen_amount'] !== null && !$order['payment_exists']);
                
                // Determine the order status to display
                $display_order_status = $order['order_status'];
                if ($order['payment_exists'] && empty($display_order_status)) {
                    $display_order_status = 'payment_processing';
                }
            ?>
            <div class="order-card" data-status="<?= htmlspecialchars($order['status']) ?>" 
                 data-search="ORD-<?= $order_number ?> <?= htmlspecialchars($order['cloud_kitchen_name']) ?> <?= htmlspecialchars($order['ord_description']) ?>">
                <div class="order-header">
                    <div class="order-info">
                        <div class="order-title-row">
                            <h3 class="order-title">Custom Order ORD-<?= $order_number ?></h3>
                            <span class="badge <?= $badge_class ?>"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span>
                        </div>
                        <p class="kitchen-name"><strong>Cloud Kitchen:</strong> <?= htmlspecialchars($order['cloud_kitchen_name']) ?></p>
                        <p class="status-message"><?= $status_message ?></p>
                    </div>
                    <div class="order-header-right">
                        <span class="order-status-badge" data-status="<?= htmlspecialchars($display_order_status) ?>">
                            <?= ucfirst(str_replace('_', ' ', $display_order_status)) ?>
                        </span>
                        <button class="toggle-details-btn" onclick="toggleOrderDetails(<?= $index + 1 ?>)">
                            <span class="toggle-text" id="toggleText<?= $index + 1 ?>">Hide Details</span>
                            <img src="https://img.icons8.com/?size=100&id=39804&format=png&color=957B6A" alt="Toggle" class="toggle-icon" id="toggleIcon<?= $index + 1 ?>">
                        </button>
                    </div>
                </div>

                <div class="order-details" id="orderDetails<?= $index + 1 ?>">
                    <div class="details-grid">
                        <div class="detail-item">
                            <img src="https://img.icons8.com/?size=100&id=udduMUcrHmZa&format=png&color=000000" alt="Calendar" class="detail-icon">
                            <div>
                                <p class="detail-label">Delivery Date</p>
                                <p class="detail-value"><?= $delivery_date ?></p>
                            </div>
                        </div>
                        <div class="detail-item">
                            <img src="https://img.icons8.com/?size=100&id=70301&format=png&color=000000" alt="Clock" class="detail-icon">
                            <div>
                                <p class="detail-label">Delivery Time</p>
                                <p class="detail-value"><?= $delivery_time ?></p>
                            </div>
                        </div>
                        <div class="detail-item">
                            <img src="https://img.icons8.com/?size=100&id=59844&format=png&color=000000" alt="Dollar" class="detail-icon">
                            <div>
                                <p class="detail-label">Budget Range (EGP)</p>
                                <p class="detail-value"><?= htmlspecialchars($order['budget_min']) ?> - <?= htmlspecialchars($order['budget_max']) ?></p>
                            </div>
                        </div>
                        <div class="detail-item">
                            <img src="https://img.icons8.com/?size=100&id=99268&format=png&color=000000" alt="Users" class="detail-icon">
                            <div>
                                <p class="detail-label">Number of People</p>
                                <p class="detail-value"><?= htmlspecialchars($order['people_servings']) ?></p>
                            </div>
                        </div>
                    
                        <?php if ($order['status'] === 'rejected'): ?> 
                        <div class="delete-section">
                            <div class="section-header">
                                <img src="https://img.icons8.com/?size=100&id=79023&format=png&color=FA5252" alt="Rejected" class="section-icon">
                                <h4>Order Rejected by Kitchen</h4>
                            </div>
                            <p class="rejection-message">This order has been rejected by the cloud kitchen.</p>
                            <button class="btn btn-delete" onclick="deleteRejectedOrder(<?= $index + 1 ?>, <?= $order['order_id'] ?>)">
                                <img src="https://img.icons8.com/?size=100&id=102350&format=png&color=FA5252" alt="Delete">
                                Delete Order
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="description-section">
                        <div class="section-header">
                            <img src="https://img.icons8.com/?size=100&id=85462&format=png&color=000000" alt="File" class="section-icon">
                            <h4>Order Description</h4>
                        </div>
                        <p class="description-text"><?= htmlspecialchars($order['ord_description']) ?></p>
                    </div>

                    <?php if (!empty($order['img_reference'])): ?>
                    <div class="reference-section">
                        <div class="section-header">
                            <img src="https://img.icons8.com/?size=100&id=112856&format=png&color=000000" alt="Image" class="section-icon">
                            <h4>Reference Image</h4>
                        </div>
                        <img src="<?= htmlspecialchars($order['img_reference']) ?>" alt="Design Reference" class="reference-image">
                    </div>
                    <?php endif; ?>

                    <?php if ($show_price_quote): ?>
                    <!-- Price Quote Section -->
                    <div class="price-quote-section">
                        <div class="section-header">
                            <img src="https://img.icons8.com/?size=100&id=106571&format=png&color=000000" alt="Tag" class="section-icon">
                            <h4>Price Quote</h4>
                        </div>
                        <div class="price-amount">EGP <?= number_format($order['chosen_amount'], 2) ?></div>
                        <p class="price-message">Please review and accept the price to proceed with your order</p>
                        
                        <div class="price-actions">
                            <button class="btn btn-reject" onclick="rejectPrice(<?= $index + 1 ?>, <?= $order['order_id'] ?>)">
                                <img src="https://img.icons8.com/?size=100&id=79023&format=png&color=FA5252" alt="X">
                                Reject Price
                            </button>
                            <button class="btn btn-accept" onclick="acceptPrice(<?= $index + 1 ?>, <?= $order['order_id'] ?>)">
                                <img src="https://img.icons8.com/?size=100&id=7690&format=png&color=40C057" alt="Check">
                                Accept Price
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($show_invoice): ?>
                    <!-- Invoice Section -->
                    <div class="invoice-section">
                        <div class="section-header">
                            <img src="https://img.icons8.com/?size=100&id=106571&format=png&color=000000" alt="Receipt" class="section-icon">
                            <h4>Order Invoice</h4>
                        </div>
                        
                        <div class="invoice-details">
                            <div class="invoice-row">
                                <span>Custom Order Price:</span>
                                <span class="invoice-amount">EGP <?= number_format($order['chosen_amount'], 2) ?></span>
                            </div>
                            <div class="invoice-row">
                                <span>Tax (7%):</span>
                                <span class="invoice-amount">EGP <?= number_format($order['chosen_amount'] * 0.07, 2) ?></span>
                            </div>
                            <div class="invoice-row">
                                <span>Delivery Fee:</span>
                                <span class="invoice-amount">EGP 50.00</span>
                            </div>
                            <hr class="invoice-divider">
                            <div class="invoice-row invoice-total">
                                <span>Total Amount:</span>
                                <span class="invoice-amount">EGP <?= number_format($order['chosen_amount'] * 1.07 + 50, 2) ?></span>
                            </div>
                        </div>
                        
                        <a href="../Custom_Order_Management/Payment/index.php?order_id=<?= $order['order_id'] ?>" class="btn btn-checkout">
                            <img src="https://img.icons8.com/?size=100&id=86620&format=png&color=FFFFFF" alt="Credit Card">
                            Proceed to Checkout
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($orders)): ?>
            <div class="no-orders">
                <img src="https://img.icons8.com/?size=100&id=13030&format=png&color=957B6A" alt="No orders" class="no-orders-icon">
                <h3>No Custom Orders Found</h3>
                <p>You haven't placed any custom orders yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include '..\global\footer\footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>