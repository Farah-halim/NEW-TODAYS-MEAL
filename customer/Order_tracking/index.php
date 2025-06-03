<?php
session_start();
require_once('../DB_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: \\NEW-TODAYS-MEAL\\Register&Login\\login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle rating form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_rating'])) {
        $order_id = $_POST['order_id'];
        $kitchen_id = $_POST['kitchen_id'];
        $stars = $_POST['stars'];

        // Insert or update existing review
        $check_stmt = $conn->prepare("SELECT review_no FROM reviews WHERE order_id = ?");
        if (!$check_stmt) { die("DB error."); }
        $check_stmt->bind_param("i", $order_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $check_stmt->close();
            $update_stmt = $conn->prepare("UPDATE reviews SET stars = ?, review_date = NOW() WHERE order_id = ?");
            $update_stmt->bind_param("ii", $stars, $order_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            $check_stmt->close();
            $stmt = $conn->prepare("INSERT INTO reviews (order_id, cloud_kitchen_id, customer_id, stars, review_date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiii", $order_id, $kitchen_id, $user_id, $stars);
            $stmt->execute();
            $stmt->close();
        }

        // Redirect to remove POST data and prevent resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . ($filter !== 'all' ? "?filter=$filter" : ""));
        exit();
    }
}

// Fetch customer info
$stmt = $conn->prepare("
    SELECT u.u_name AS username, u.phone, eu.address
    FROM users u
    JOIN external_user eu ON u.user_id = eu.user_id
    JOIN customer c ON eu.user_id = c.user_id
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$customer_result = $stmt->get_result();

if ($customer_result->num_rows > 0) {
    $customer = $customer_result->fetch_assoc();
    $username = $customer['username'];
    $address = $customer['address'];
    $phone = $customer['phone'];
} else {
    $username = 'Customer';
    $address = 'Address not specified';
    $phone = '';
}

// Filter for order list
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$status_conditions = [
    'all' => "",
    'preparing' => "AND (o.order_status = 'pending' OR o.order_status = 'preparing')",
    'on-the-way' => "AND (o.order_status = 'ready_for_pickup' OR o.order_status = 'in_transit')",
    'delivered' => "AND o.order_status = 'delivered'",
    'cancelled' => "AND o.order_status = 'cancelled'"
];
$status_condition = $status_conditions[$filter] ?? "";

// Check if we need to show order details modal
$show_order_details = isset($_GET['view_order']);
$order_details = null;
if ($show_order_details) {
    $order_id = $_GET['view_order'];
    $query = "SELECT o.order_id, o.order_date, o.order_status, o.total_price, 
                     o.cloud_kitchen_id as kitchen_id, ck.business_name as kitchen_name,
                     pd.delivery_fees, pd.total_payment, o.delivery_type, o.customer_selected_date
              FROM orders o
              JOIN payment_details pd ON o.order_id = pd.order_id
              JOIN cloud_kitchen_owner ck ON o.cloud_kitchen_id = ck.user_id
              WHERE o.customer_id = ? AND o.order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order_details = $result->fetch_assoc();

    if ($order_details) {
        $query = "SELECT m.name, m.photo, oc.quantity, oc.price 
                  FROM order_content oc
                  JOIN meals m ON oc.meal_id = m.meal_id
                  WHERE oc.order_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        $order_details['items'] = $items_result->fetch_all(MYSQLI_ASSOC);
    }
}

// Check if we need to show rating modal
$show_rating_modal = isset($_GET['rate_order']);
$rating_order = null;
if ($show_rating_modal) {
    $order_id = $_GET['rate_order'];
    $query = "SELECT o.order_id, o.cloud_kitchen_id as kitchen_id, ck.business_name as kitchen_name
              FROM orders o
              JOIN cloud_kitchen_owner ck ON o.cloud_kitchen_id = ck.user_id
              WHERE o.customer_id = ? AND o.order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rating_order = $result->fetch_assoc();
}

// Fetch all orders with their review info
$order_types_filter = "AND (o.ord_type = 'normal' OR (o.ord_type = 'scheduled' AND o.delivery_type IN ('all_at_once', 'daily_delivery')))";
$orders_query = "SELECT o.order_id, o.order_date, o.order_status, o.total_price, 
                 o.cloud_kitchen_id as kitchen_id, ck.business_name as kitchen_name,
                 pd.delivery_fees, pd.total_payment, o.delivery_type, o.customer_selected_date, o.ord_type,
                 IFNULL(r.stars, 0) AS rating_stars
          FROM orders o
          LEFT JOIN payment_details pd ON o.order_id = pd.order_id
          LEFT JOIN reviews r ON o.order_id = r.order_id
          JOIN cloud_kitchen_owner ck ON o.cloud_kitchen_id = ck.user_id
          WHERE o.customer_id = ? $status_condition $order_types_filter
          ORDER BY o.order_date DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);

$status_map = [
    'pending' => 'preparing',
    'preparing' => 'preparing',
    'ready_for_pickup' => 'on-the-way',
    'in_transit' => 'on-the-way',
    'delivered' => 'delivered',
    'cancelled' => 'cancelled'
];

foreach ($orders as &$order) {
    if ($order['ord_type'] === 'scheduled' && $order['delivery_type'] === 'daily_delivery') {
        // Handle daily delivery packages
        $pkg_query = "SELECT p.package_id, p.package_name, p.delivery_date, p.package_price, p.package_status
                      FROM order_packages p
                      WHERE p.order_id = ?
                      ORDER BY p.delivery_date ASC";
        $pkg_stmt = $conn->prepare($pkg_query);
        $pkg_stmt->bind_param("i", $order['order_id']);
        $pkg_stmt->execute();
        $pkg_res = $pkg_stmt->get_result();
        $packages = [];
        while ($pkg = $pkg_res->fetch_assoc()) {
            $package_id = $pkg['package_id'];
            $meals_query = "SELECT m.name, mip.quantity, mip.price 
                            FROM meals_in_each_package mip
                            JOIN meals m ON m.meal_id = mip.meal_id
                            WHERE mip.package_id = ?";
            $meals_stmt = $conn->prepare($meals_query);
            $meals_stmt->bind_param("i", $package_id);
            $meals_stmt->execute();
            $meals_res = $meals_stmt->get_result();
            $meals = [];
            while ($meal = $meals_res->fetch_assoc()) {
                $meals[] = $meal;
            }
            $meals_stmt->close();
            $packages[] = [
                'package_id' => $package_id,
                'package_name' => $pkg['package_name'],
                'delivery_date' => $pkg['delivery_date'],
                'package_price' => $pkg['package_price'],
                'package_status' => $pkg['package_status'],
                'items' => $meals,
            ];
        }
        $pkg_stmt->close();
        $order['packages'] = $packages;
        $order['items'] = [];
    } else {
        // Handle normal orders and all-at-once scheduled orders
        $query = "SELECT m.name, m.photo, oc.quantity, oc.price 
                  FROM order_content oc
                  JOIN meals m ON oc.meal_id = m.meal_id
                  WHERE oc.order_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $order['order_id']);
        $stmt->execute();
        $items_result = $stmt->get_result();
        $order['items'] = $items_result->fetch_all(MYSQLI_ASSOC);
    }

    $order['show_items'] = (isset($_GET['show_items']) && (int)$_GET['show_items'] === $order['order_id'] && (!isset($_GET['hide_items']) || (int)$_GET['hide_items'] !== $order['order_id']));
    
    $order['delivery_type'] = $order['delivery_type'] ?? 'all_at_once';
    $order['customer_selected_date'] = $order['customer_selected_date'] ?? null;
    
    $order['status_class'] = $status_map[$order['order_status']] ?? 'preparing';
}
unset($order);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Order Track Connect</title>
<link rel="stylesheet" href="styles.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<style>
    /* Rating Modal Styles */
.modal {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal.show {
    display: flex;
}
.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    max-width: 400px;
    width: 90%;
    position: relative;
}
.close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
    color: #444;
}
.star-rating {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin: 1.5rem 0;
}
.star-rating .star {
    font-size: 3rem;
    cursor: pointer;
    color: #ccc;
    user-select: none;
}
.star-rating .star.active {
    color: gold;
}
.btn-primary, .btn-secondary {
    text-decoration: none;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.rating-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
}
.rated-badge {
    font-weight: 600;
    color:rgb(238, 185, 25);
    cursor: default;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
/* Minimal modal styling for clarity */
.modal {
    position: fixed;
    top: 0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    padding: 1.5rem;
    max-width: 600px;
    width: 90%;
    border-radius: 8px;
    position: relative;
}
.modal-header h2 {
    margin: 0 0 0.5rem 0;
}
.close, .close-btn {
    position: absolute;
    top: 0.7rem;
    right: 1rem;
    font-size: 1.5rem;
    text-decoration: none;
    color: #333;
}
.star-rating .star {
    font-size: 2rem;
    cursor: pointer;
    color: #ccc;
}
.star-rating .star.active {
    color: gold;
}
</style>
</head>
<body>
<?php include '..\global\navbar\navbar.php'; ?>

<div class="container">
    <div class="filter-tabs">
        <a href="?filter=all" class="tab-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All Orders</a>
        <a href="?filter=preparing" class="tab-btn <?php echo $filter === 'preparing' ? 'active' : ''; ?>">Preparing</a>
        <a href="?filter=on-the-way" class="tab-btn <?php echo $filter === 'on-the-way' ? 'active' : ''; ?>">On the way</a>
        <a href="?filter=delivered" class="tab-btn <?php echo $filter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
        <a href="?filter=cancelled" class="tab-btn <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
    </div>

    <div class="orders-list">
        <?php 
        $status_map = [
            'pending' => 'preparing',
            'preparing' => 'preparing',
            'ready_for_pickup' => 'on-the-way',
            'in_transit' => 'on-the-way',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled'
        ];
        foreach ($orders as $order):
            $status_class = $status_map[$order['order_status']] ?? 'preparing';
            $order_date = new DateTime($order['order_date']);
            $is_scheduled = isset($order['delivery_type']) && $order['delivery_type'] === 'all_at_once' && isset($order['customer_selected_date']);
        ?>
        <div class="order-card" data-status="<?php echo $status_class; ?>" data-order-type="<?php echo $is_scheduled ? 'scheduled' : 'normal'; ?>">
            <div class="order-header">
                <div class="restaurant-icon">
                    <img alt="<?php echo htmlspecialchars($order['kitchen_name']); ?>" src="caterer.jpg" />
                </div>
                <div class="order-info">
                    <h3><?php echo htmlspecialchars($order['kitchen_name']); ?> 
                        <span class="order-time"> • <?php echo $order_date->format('M j, Y'); ?> • <?php echo $order_date->format('g:i A'); ?></span> 
                        <?php if ($is_scheduled): ?>
                            <span class="order-type">Scheduled</span></h3>
                            <p class="order-id">Order ID: <?php echo $order['order_id']; ?></p>
                            <p class="delivery-type">Delivery Type: All at once</p>
                            <?php if ($order['customer_selected_date']): ?>
                                <?php $delivery_date = new DateTime($order['customer_selected_date']); ?>
                                <p class="delivery-date">Delivery Date Should be: <?php echo $delivery_date->format('M j, Y'); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="order-type">Normal</span></h3>
                            <p class="order-id">Order ID: <?php echo $order['order_id']; ?></p>
                        <?php endif; ?>
                    <div class="items-list <?php echo $order['show_items'] ? 'show' : ''; ?>">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="item-preview">
                            <span class="item-name"><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                            <span class="item-price">EGP <?php echo number_format($item['price'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="status-wrap">
                    <div class="status-badge <?php echo $status_class; ?>">
                        <?php 
                        echo ucwords(str_replace('-', ' ', $status_class));
                        if ($status_class == 'on-the-way') echo ' (In Transit)';
                        ?>
                    </div>
                    <?php if ($order['show_items']): ?>
                        <a href="?hide_items=<?php echo $order['order_id']; ?><?php if($filter !== 'all') echo '&filter='.$filter; ?>" class="items-toggle active"><?php echo count($order['items']); ?> items <i class="fas fa-chevron-up"></i></a>
                    <?php else: ?>
                        <a href="?show_items=<?php echo $order['order_id']; ?><?php if($filter !== 'all') echo '&filter='.$filter; ?>" class="items-toggle"><?php echo count($order['items']); ?> items <i class="fas fa-chevron-down"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="order-actions <?php echo $status_class == 'delivered' ? 'delivered-actions' : ''; ?>">
                <a href="?view_order=<?php echo $order['order_id']; ?><?php if($filter !== 'all') echo '&filter='.$filter; ?>" class="btn-secondary <?php echo $status_class == 'delivered' ? 'small' : ''; ?>">View Details</a>
                <?php if ($status_class == 'delivered'): ?>
                    <?php if ($order['rating_stars'] > 0): ?>
                        <div class="rated-badge"><span>Rated: <?php echo $order['rating_stars']; ?>&#9733;</span></div>
                    <?php else: ?>
                        <a href="?rate_order=<?php echo $order['order_id']; ?><?php if($filter !== 'all') echo '&filter='.$filter; ?>" class="btn-primary">Rate Order</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Order Details Modal -->
<?php if ($show_order_details && $order_details): ?>
<div class="modal" role="dialog" aria-modal="true" aria-labelledby="orderDetailsTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="orderDetailsTitle">Order Details</h2>
            <a href="?<?php echo $filter !== 'all' ? 'filter='.$filter : ''; ?>" aria-label="Close modal" class="close">&times;</a>
        </div>
        <div class="modal-body">
            <div class="customer-info">
                <h3>Customer Information</h3>
                <div class="info-item">
                    <span class="icon" aria-hidden="true"><i class="fas fa-user"></i></span>
                    <span id="customerName"><?php echo htmlspecialchars($username); ?></span>
                </div>
                <div class="info-item">
                    <span class="icon" aria-hidden="true"><i class="fas fa-map-marker-alt"></i></span>
                    <span id="customerAddress"><?php echo htmlspecialchars($address); ?></span>
                </div>
            </div>

            <?php if (isset($order_details['delivery_type']) && $order_details['delivery_type'] === 'all_at_once' && isset($order_details['customer_selected_date'])): ?>
                <div class="delivery-info">
                    <h3>Delivery Information</h3>
                    <div class="info-item">
                        <span class="icon" aria-hidden="true"><i class="fas fa-truck"></i></span>
                        <span>Delivery Type: All at once</span>
                    </div>
                    <div class="info-item">
                        <span class="icon" aria-hidden="true"><i class="fas fa-calendar-alt"></i></span>
                        <span>Scheduled Delivery Date: <?php echo (new DateTime($order_details['customer_selected_date']))->format('M j, Y'); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="delivery-person" id="deliveryPersonSection" style="<?php echo ($status_map[$order_details['order_status']] ?? '') === 'on-the-way' ? 'display:block;' : 'display:none;'; ?>">
                <h3>Delivery Person</h3>
                <div class="delivery-info">
                    <div class="delivery-avatar" aria-hidden="true">AH</div>
                    <span class="delivery-name">Ahmed Hassan</span>
                    <a href="tel:<?php echo htmlspecialchars($phone); ?>" class="call-btn" aria-label="Call delivery person"><i class="fas fa-phone"></i> Call</a>
                </div>
            </div>

            <div class="order-items">
                <h3>Order Items</h3>
                <?php foreach ($order_details['items'] as $item): ?>
                <div class="item">
                    <span class="item-name"><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                    <span class="item-price">EGP <?php echo number_format($item['price'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="payment-summary">
                <h3>Payment Summary</h3>
                <div class="summary-item">
                    <span>Subtotal</span>
                    <span id="subtotal">EGP <?php echo number_format($order_details['total_price'], 2); ?></span>
                </div>
                <div class="summary-item">
                    <span>Delivery Fee</span>
                    <span id="deliveryFee">EGP <?php echo number_format($order_details['delivery_fees'], 2); ?></span>
                </div>
                <div class="summary-item total">
                    <span>Total</span>
                    <span id="total">EGP <?php echo number_format($order_details['total_payment'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Rating Modal -->
<?php if ($show_rating_modal && $rating_order): ?>
<div class="modal" role="dialog" aria-modal="true" aria-labelledby="ratingModalTitle" id="ratingModal">
    <div class="modal-content rating-modal">
        <div class="modal-header">
            <a href="?<?php echo $filter !== 'all' ? 'filter='.$filter : ''; ?>" aria-label="Close rating modal" class="close-btn">&times;</a>
        </div>
        <div class="modal-body">
            <h2 id="ratingModalTitle">Rate Your Order</h2>
            <p>How was your experience with <strong><?php echo htmlspecialchars($rating_order['kitchen_name']); ?></strong>?</p>
            <form id="ratingForm" method="POST" action="">
                <input type="hidden" name="order_id" value="<?php echo $rating_order['order_id']; ?>">
                <input type="hidden" name="kitchen_id" value="<?php echo $rating_order['kitchen_id']; ?>">
                <input type="hidden" name="stars" id="ratingStars" value="0">

                <div class="star-rating" role="radiogroup" aria-label="Star rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span 
                            role="radio" 
                            tabindex="0" 
                            aria-checked="false" 
                            class="star" 
                            data-value="<?php echo $i; ?>"
                            onclick="setRating(<?php echo $i; ?>)" 
                            onkeydown="if(event.key==='Enter' || event.key===' ') setRating(<?php echo $i; ?>);"
                        >&#9733;</span>
                    <?php endfor; ?>
                </div>
                <div class="rating-actions">
                    <a href="?<?php echo $filter !== 'all' ? 'filter='.$filter : ''; ?>" class="btn-secondary">Cancel</a>
                    <button type="submit" name="submit_rating" class="btn-primary">Submit Rating</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include '..\global\footer\footer.php'; ?>

<script>
// Star rating selection for accessibility & UI
function setRating(value) {
    document.getElementById('ratingStars').value = value;
    const stars = document.querySelectorAll('.star-rating .star');
    stars.forEach((star, index) => {
        if(index < value) {
            star.classList.add('active');
            star.setAttribute('aria-checked', 'true');
        } else {
            star.classList.remove('active');
            star.setAttribute('aria-checked', 'false');
        }
    });
}
</script>
</body>
</html>