<?php
include "../DB_connection.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: \NEW-TODAYS-MEAL\Register&Login\login.php");
    exit();
}

// Get customer ID from session
$customer_id = $_SESSION['user_id'];

// Fetch customer's customized orders with all statuses
$orders = [];
if ($conn) {
    $sql = "SELECT co.*, o.order_date, o.delivery_date, o.delivery_zone, 
                   cko.business_name AS cloud_kitchen_name,
                   u.u_name AS customer_name,
                   o.order_status AS order_status
            FROM customized_order co
            JOIN orders o ON co.order_id = o.order_id
            JOIN cloud_kitchen_owner cko ON co.kitchen_id = cko.user_id
            JOIN users u ON co.customer_id = u.user_id
            WHERE co.customer_id = ?
            ORDER BY o.order_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Map the database status to our simplified status system
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
</head>
<body>
           <?php include '..\global\navbar\navbar.php'; ?>

    <div class="container">
        <!-- Search and Filter Section -->
<div class="search-filter-section">
    <div class="search-bar">
        <div class="search-input-wrapper">
            <img src="https://img.icons8.com/?size=100&id=3159&format=png&color=000000" alt="Search" class="search-icon">
            <input type="text" id="searchInput" placeholder="Search your orders..." class="search-input">
        </div>
    </div>

    <!-- Filter Buttons Only -->
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


        <!-- Orders List -->
        <div class="orders-list" id="ordersList">
            <?php foreach ($orders as $index => $order): 
                $order_number = str_pad($order['order_id'], 3, '0', STR_PAD_LEFT);
                $delivery_date = date('Y-m-d', strtotime($order['delivery_date']));
                $delivery_time = date('g:i A', strtotime($order['delivery_date']));
                $order_date = date('Y-m-d', strtotime($order['order_date']));
                
                // Determine badge class and status message based on order status
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
                        $status_message = 'Price accepted - Ready for checkout';
                        break;
                }
                
                // Determine if price quote section should be shown
                $show_price_quote = ($order['status'] == 'approved' && $order['chosen_amount'] !== null);
                
                // Determine if invoice section should be shown
                $show_invoice = ($order['status'] == 'price_accepted' && $order['chosen_amount'] !== null);
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
                    <button class="toggle-details-btn" onclick="toggleOrderDetails(<?= $index + 1 ?>)">
                        <span class="toggle-text" id="toggleText<?= $index + 1 ?>">Hide Details</span>
                        <img src="https://img.icons8.com/?size=100&id=39804&format=png&color=957B6A" alt="Toggle" class="toggle-icon" id="toggleIcon<?= $index + 1 ?>">
                    </button>
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
                    
                        <!-- Delete Button Section -->
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