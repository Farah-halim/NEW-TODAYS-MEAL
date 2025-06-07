<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: manage_categories.php');
    exit();
}

require_once 'db_connect.php';

$customerId = $_GET['id'] ?? null;

if (!$customerId) {
    header('Location: manage_customers.php');
    exit();
}

// Get customer details with zone information
$customerQuery = "SELECT c.*, u.u_name, u.mail, u.phone, u.created_at as user_created_at, 
                         eu.address, eu.latitude, eu.longitude, z.name as zone_name
                  FROM customer c
                  JOIN users u ON c.user_id = u.user_id  
                  JOIN external_user eu ON c.user_id = eu.user_id
                  LEFT JOIN zones z ON eu.zone_id = z.zone_id
                  WHERE c.user_id = ?";
$customerStmt = $pdo->prepare($customerQuery);
$customerStmt->execute([$customerId]);
$customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    header('Location: manage_customers.php');
    exit();
}

// Get comprehensive order statistics
$orderStatsQuery = "SELECT 
                        COUNT(*) as total_orders,
                        COALESCE(SUM(total_price), 0) as total_spent,
                        AVG(total_price) as avg_order_value,
                        MAX(order_date) as last_order_date,
                        MIN(order_date) as first_order_date,
                        COUNT(CASE WHEN order_status = 'delivered' THEN 1 END) as delivered_orders,
                        COUNT(CASE WHEN order_status = 'pending' THEN 1 END) as pending_orders,
                        COUNT(CASE WHEN order_status = 'cancelled' THEN 1 END) as cancelled_orders,
                        COUNT(CASE WHEN ord_type = 'customized' THEN 1 END) as custom_orders,
                        COUNT(CASE WHEN DATE(order_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as orders_last_30_days
                    FROM orders 
                    WHERE customer_id = ?";
$orderStatsStmt = $pdo->prepare($orderStatsQuery);
$orderStatsStmt->execute([$customerId]);
$orderStats = $orderStatsStmt->fetch(PDO::FETCH_ASSOC);

// Get review statistics
$reviewStatsQuery = "SELECT 
                        COUNT(*) as total_reviews,
                        AVG(stars) as avg_rating,
                        COUNT(CASE WHEN stars = 5 THEN 1 END) as five_star_reviews,
                        COUNT(CASE WHEN stars = 4 THEN 1 END) as four_star_reviews,
                        COUNT(CASE WHEN stars = 3 THEN 1 END) as three_star_reviews,
                        COUNT(CASE WHEN stars = 2 THEN 1 END) as two_star_reviews,
                        COUNT(CASE WHEN stars = 1 THEN 1 END) as one_star_reviews,
                        MAX(review_date) as last_review_date,
                        MIN(review_date) as first_review_date
                    FROM reviews 
                    WHERE customer_id = ?";
$reviewStatsStmt = $pdo->prepare($reviewStatsQuery);
$reviewStatsStmt->execute([$customerId]);
$reviewStats = $reviewStatsStmt->fetch(PDO::FETCH_ASSOC);

// Get comprehensive order history
$orderHistoryQuery = "SELECT o.*, cko.business_name,
                             (SELECT COUNT(*) FROM order_content oc WHERE oc.order_id = o.order_id) as items_count,
                             (SELECT GROUP_CONCAT(m.name SEPARATOR ', ') 
                              FROM order_content oc 
                              JOIN meals m ON oc.meal_id = m.meal_id 
                              WHERE oc.order_id = o.order_id 
                              LIMIT 3) as meal_names
                      FROM orders o
                      LEFT JOIN cloud_kitchen_owner cko ON o.cloud_kitchen_id = cko.user_id
                      WHERE o.customer_id = ?
                      ORDER BY o.order_date DESC
                      LIMIT 15";
$orderHistoryStmt = $pdo->prepare($orderHistoryQuery);
$orderHistoryStmt->execute([$customerId]);
$orderHistory = $orderHistoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get detailed reviews given by customer
$reviewsQuery = "SELECT r.*, cko.business_name, o.total_price as order_value
                 FROM reviews r
                 JOIN cloud_kitchen_owner cko ON r.cloud_kitchen_id = cko.user_id
                 LEFT JOIN orders o ON r.order_id = o.order_id
                 WHERE r.customer_id = ?
                 ORDER BY r.review_date DESC
                 LIMIT 10";
$reviewsStmt = $pdo->prepare($reviewsQuery);
$reviewsStmt->execute([$customerId]);
$reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get cart items
$cartQuery = "SELECT ci.quantity, m.name as meal_name, m.price, cko.business_name
              FROM cart c
              JOIN cart_items ci ON c.cart_id = ci.cart_id
              JOIN meals m ON ci.meal_id = m.meal_id
              JOIN cloud_kitchen_owner cko ON m.cloud_kitchen_id = cko.user_id
              WHERE c.customer_id = ?";
$cartStmt = $pdo->prepare($cartQuery);
$cartStmt->execute([$customerId]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

// Get customized orders
$customOrdersQuery = "SELECT co.*, cko.business_name
                      FROM customized_order co
                      JOIN cloud_kitchen_owner cko ON co.kitchen_id = cko.user_id
                      WHERE co.customer_id = ?
                      ORDER BY co.created_at DESC";
$customOrdersStmt = $pdo->prepare($customOrdersQuery);
$customOrdersStmt->execute([$customerId]);
$customOrders = $customOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate customer age
$age = $customer['BOD'] ? (date('Y') - date('Y', strtotime($customer['BOD']))) : 'N/A';

// Calculate time since registration
function getTimeSinceRegistration($registrationDate) {
    if (!$registrationDate) return 'Unknown';
    
    $now = new DateTime();
    $registration = new DateTime($registrationDate);
    $interval = $now->diff($registration);
    
    if ($interval->y > 0) {
        return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . 
               ($interval->m > 0 ? ', ' . $interval->m . ' month' . ($interval->m > 1 ? 's' : '') : '');
    } elseif ($interval->m > 0) {
        return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . 
               ($interval->d > 0 ? ', ' . $interval->d . ' day' . ($interval->d > 1 ? 's' : '') : '');
    } elseif ($interval->d > 0) {
        return $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
    } elseif ($interval->h > 0) {
        return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
    } else {
        return 'Less than an hour';
    }
}

$timeSinceRegistration = getTimeSinceRegistration($customer['registration_date']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Details - <?php echo htmlspecialchars($customer['u_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #e57e24;
            --secondary: #dab98b;
            --accent: #3d6f5d;
            --background: #fff7e5;
        }
        
        body {
            background: linear-gradient(135deg, #fff7e5 0%, #fef3dc 50%, #f5e0c2 100%);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #6a4125;
        }
        
        .navbar {
            background: linear-gradient(135deg, #e57e24 0%, #d16919 100%);
            box-shadow: 0 4px 20px rgba(229, 126, 36, 0.3);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            background: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }
        
        .stats-card {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--accent) 0%, #2c5547 100%);
            color: white;
            border-radius: 15px;
            margin-bottom: 1rem;
        }
        
        .stats-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .stats-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: rgba(229, 126, 36, 0.05);
            border-radius: 10px;
            border-left: 4px solid var(--primary);
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, #d16919 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            font-size: 1.1rem;
        }
        
        .section-header {
            background: linear-gradient(135deg, var(--primary) 0%, #d16919 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px 10px 0 0;
            margin: -1.25rem -1.25rem 1.5rem -1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-active {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-blocked {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        
        .order-item {
            background: rgba(61, 111, 93, 0.05);
            border: 1px solid rgba(61, 111, 93, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .order-item:hover {
            background: rgba(61, 111, 93, 0.1);
            transform: translateX(5px);
        }
        
        .review-item {
            background: rgba(229, 126, 36, 0.05);
            border: 1px solid rgba(229, 126, 36, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .rating {
            color: #fbbf24;
            font-size: 1.1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, #2c5547 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(61, 111, 93, 0.3);
        }
        
        .timeline-item {
            background: rgba(229, 126, 36, 0.05);
            border-left: 4px solid var(--primary);
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
            transition: all 0.3s ease;
        }
        
        .timeline-item:hover {
            background: rgba(229, 126, 36, 0.1);
            transform: translateX(5px);
        }
        
        .order-status-delivered {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .order-status-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .order-status-cancelled {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .stats-mini {
            text-align: center;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, #d16919 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .stats-mini-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stats-mini-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .review-distribution {
            background: rgba(61, 111, 93, 0.05);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .rating-bar {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .rating-label {
            width: 60px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .rating-progress {
            flex: 1;
            height: 20px;
            background: #e5e7eb;
            border-radius: 10px;
            margin: 0 10px;
            overflow: hidden;
        }
        
        .rating-fill {
            height: 100%;
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
            transition: width 0.5s ease;
        }
        
        .rating-count {
            width: 40px;
            text-align: right;
            font-size: 0.9rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <span class="navbar-brand">
                <i class="fas fa-user me-2"></i>Customer Details - <?php echo htmlspecialchars($customer['u_name']); ?>
            </span>
            <div>
                <a href="manage_customers.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Customers
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Customer Info Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="section-header">
                            <i class="fas fa-user"></i>Customer Information
                        </h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <strong>Name:</strong><br>
                                        <?php echo htmlspecialchars($customer['u_name']); ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <strong>Email:</strong><br>
                                        <?php echo htmlspecialchars($customer['mail']); ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div>
                                        <strong>Phone:</strong><br>
                                        <?php echo htmlspecialchars($customer['phone']); ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <strong>Member Since:</strong><br>
                                        <?php echo date('M j, Y', strtotime($customer['registration_date'])); ?><br>
                                        <small class="text-muted"><?php echo $timeSinceRegistration; ?> ago</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-<?php echo $customer['gender'] == 'Male' ? 'mars' : 'venus'; ?>"></i>
                                    </div>
                                    <div>
                                        <strong>Gender & Age:</strong><br>
                                        <?php echo htmlspecialchars($customer['gender']); ?>, <?php echo $age; ?> years old
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <strong>Address & Zone:</strong><br>
                                        <?php echo htmlspecialchars($customer['address']); ?><br>
                                        <?php if (!empty($customer['zone_name'])): ?>
                                            <span class="badge bg-primary mt-1">
                                                <i class="fas fa-map-pin me-1"></i><?php echo htmlspecialchars($customer['zone_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div>
                                        <strong>Status:</strong><br>
                                        <span class="status-<?php echo $customer['status']; ?>">
                                            <?php echo ucfirst($customer['status']); ?>
                                        </span>
                                        <?php if ($customer['is_subscribed']): ?>
                                            <span class="badge bg-info ms-2">
                                                <i class="fas fa-crown"></i> Subscribed
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <strong>Last Login:</strong><br>
                                        <?php if ($customer['last_login']): ?>
                                            <?php echo date('M j, Y \a\t H:i', strtotime($customer['last_login'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Never logged in</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stats-value"><?php echo number_format($orderStats['total_orders'] ?? 0); ?></div>
                    <div class="stats-label">Total Orders</div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stats-value">EGP <?php echo number_format($orderStats['total_spent'] ?? 0, 0); ?></div>
                    <div class="stats-label">Total Spent</div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stats-value"><?php echo number_format($reviewStats['avg_rating'] ?? 0, 1); ?></div>
                    <div class="stats-label">Avg Rating Given</div>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="section-header">
                            <i class="fas fa-chart-bar"></i>Order Statistics
                        </h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stats-mini">
                                    <div class="stats-mini-value"><?php echo $orderStats['delivered_orders'] ?? 0; ?></div>
                                    <div class="stats-mini-label">Delivered</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-mini">
                                    <div class="stats-mini-value"><?php echo $orderStats['pending_orders'] ?? 0; ?></div>
                                    <div class="stats-mini-label">Pending</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-mini">
                                    <div class="stats-mini-value"><?php echo $orderStats['custom_orders'] ?? 0; ?></div>
                                    <div class="stats-mini-label">Custom Orders</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-mini">
                                    <div class="stats-mini-value"><?php echo $orderStats['orders_last_30_days'] ?? 0; ?></div>
                                    <div class="stats-mini-label">Last 30 Days</div>
                                </div>
                            </div>
                        </div>
                        <?php if ($orderStats['avg_order_value']): ?>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><strong>Average Order Value:</strong></span>
                                    <span class="fw-bold text-primary">EGP <?php echo number_format($orderStats['avg_order_value'], 0); ?></span>
                                </div>
                                <?php if ($orderStats['first_order_date']): ?>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span><strong>First Order:</strong></span>
                                        <span><?php echo date('M j, Y', strtotime($orderStats['first_order_date'])); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($orderStats['last_order_date']): ?>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span><strong>Last Order:</strong></span>
                                        <span><?php echo date('M j, Y', strtotime($orderStats['last_order_date'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="section-header">
                            <i class="fas fa-star"></i>Review Statistics
                        </h5>
                        <?php if ($reviewStats['total_reviews'] > 0): ?>
                            <div class="text-center mb-3">
                                <div class="stats-value" style="color: var(--primary); font-size: 2rem;">
                                    <?php echo $reviewStats['total_reviews']; ?>
                                </div>
                                <div class="stats-label">Total Reviews Given</div>
                            </div>
                            <div class="review-distribution">
                                <?php 
                                $maxReviews = max($reviewStats['five_star_reviews'], $reviewStats['four_star_reviews'], 
                                                $reviewStats['three_star_reviews'], $reviewStats['two_star_reviews'], 
                                                $reviewStats['one_star_reviews']);
                                for ($i = 5; $i >= 1; $i--): 
                                    $reviewCount = $reviewStats[$i === 5 ? 'five_star_reviews' : 
                                                             ($i === 4 ? 'four_star_reviews' : 
                                                             ($i === 3 ? 'three_star_reviews' : 
                                                             ($i === 2 ? 'two_star_reviews' : 'one_star_reviews')))];
                                    $percentage = $maxReviews > 0 ? ($reviewCount / $maxReviews) * 100 : 0;
                                ?>
                                    <div class="rating-bar">
                                        <div class="rating-label"><?php echo $i; ?> ★</div>
                                        <div class="rating-progress">
                                            <div class="rating-fill" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <div class="rating-count"><?php echo $reviewCount; ?></div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-star-o fa-3x mb-3" style="opacity: 0.3;"></i>
                                <div>No reviews given yet</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="section-header">
                            <i class="fas fa-history"></i>Order History
                        </h5>
                        <?php if (!empty($orderHistory)): ?>
                            <div class="row">
                                <?php foreach ($orderHistory as $order): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="timeline-item">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong>Order #<?php echo $order['order_id']; ?></strong>
                                                    <span class="order-status-<?php echo $order['order_status']; ?> ms-2">
                                                        <?php echo ucfirst($order['order_status']); ?>
                                                    </span>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold text-primary">EGP <?php echo number_format($order['total_price'], 0); ?></div>
                                                    <small class="text-muted"><?php echo date('M j, Y H:i', strtotime($order['order_date'])); ?></small>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Kitchen:</strong> <?php echo htmlspecialchars($order['business_name'] ?? 'Unknown'); ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Type:</strong> 
                                                <span class="badge bg-<?php echo $order['ord_type'] === 'customized' ? 'warning' : 'info'; ?>">
                                                    <?php echo ucfirst($order['ord_type']); ?>
                                                </span>
                                                <span class="badge bg-secondary ms-1">
                                                    <?php echo $order['items_count']; ?> item<?php echo $order['items_count'] > 1 ? 's' : ''; ?>
                                                </span>
                                            </div>
                                            <?php if ($order['meal_names']): ?>
                                                <div class="text-muted">
                                                    <small>
                                                        <strong>Items:</strong> <?php echo htmlspecialchars($order['meal_names']); ?>
                                                        <?php if ($order['items_count'] > 3): ?>
                                                            <em>and <?php echo ($order['items_count'] - 3); ?> more...</em>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-shopping-bag fa-3x mb-3" style="opacity: 0.3;"></i>
                                <div>No orders found</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Given -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="section-header">
                            <i class="fas fa-star"></i>Reviews Given by Customer
                        </h5>
                        <?php if (!empty($reviews)): ?>
                            <div class="row">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="review-item">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($review['business_name']); ?></strong>
                                                </div>
                                                <div class="text-end">
                                                    <div class="rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star" style="color: <?php echo $i <= $review['stars'] ? '#fbbf24' : '#e5e7eb'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($review['review_date'])); ?></small>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Order #<?php echo $review['order_id']; ?></strong>
                                                <?php if ($review['order_value']): ?>
                                                    <span class="text-muted">- EGP <?php echo number_format($review['order_value'], 0); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted">
                                                <small>Review Date: <?php echo date('M j, Y \a\t H:i', strtotime($review['review_date'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-star-o fa-3x mb-3" style="opacity: 0.3;"></i>
                                <div>No reviews given yet</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 