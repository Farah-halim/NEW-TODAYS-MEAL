<?php
// Include database connection
require_once 'connection.php';

// Get kitchen ID from URL parameter
$kitchen_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($kitchen_id <= 0) {
    die("Invalid kitchen ID");
}

// Fetch basic kitchen information
$kitchenQuery = "SELECT u.user_id, u.u_name, u.mail, u.phone, 
                    ck.business_name, eu.address, ck.start_year, ck.years_of_experience, 
                    ck.orders_count, ck.average_rating, ck.status, ck.c_n_id, 
                    ck.customized_orders, ck.registration_date, ck.is_approved,
                    eu.latitude, eu.longitude, z.name as zone_name,
                    c.c_name AS speciality_name
                FROM cloud_kitchen_owner ck
                JOIN users u ON ck.user_id = u.user_id
                JOIN external_user eu ON ck.user_id = eu.user_id
                LEFT JOIN zones z ON eu.zone_id = z.zone_id
                JOIN category c ON ck.speciality_id = c.cat_id
                WHERE ck.user_id = ?";

$kitchenStmt = $conn->prepare($kitchenQuery);
$kitchenStmt->bind_param("i", $kitchen_id);
$kitchenStmt->execute();
$kitchenResult = $kitchenStmt->get_result();

if ($kitchenResult->num_rows == 0) {
    die("Kitchen not found");
}

$kitchenData = $kitchenResult->fetch_assoc();

// Fetch all categories the kitchen specializes in
$categoriesQuery = "SELECT c.cat_id, c.c_name 
                    FROM cloud_kitchen_specialist_category cksc
                    JOIN category c ON cksc.cat_id = c.cat_id
                    WHERE cksc.cloud_kitchen_id = ?";

$categoriesStmt = $conn->prepare($categoriesQuery);
$categoriesStmt->bind_param("i", $kitchen_id);
$categoriesStmt->execute();
$categoriesResult = $categoriesStmt->get_result();
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch dietary tags
$tagsQuery = "SELECT dt.tag_id, dt.tag_name 
              FROM caterer_tags ct
              JOIN dietary_tags dt ON ct.tag_id = dt.tag_id
              WHERE ct.user_id = ?";

$tagsStmt = $conn->prepare($tagsQuery);
$tagsStmt->bind_param("i", $kitchen_id);
$tagsStmt->execute();
$tagsResult = $tagsStmt->get_result();
$tags = [];
while ($row = $tagsResult->fetch_assoc()) {
    $tags[] = $row;
}

// Fetch meals offered by the kitchen
$mealsQuery = "SELECT m.meal_id, m.name, m.description, m.photo, m.price, m.stock_quantity, m.status
               FROM meals m
               WHERE m.cloud_kitchen_id = ?";

$mealsStmt = $conn->prepare($mealsQuery);
$mealsStmt->bind_param("i", $kitchen_id);
$mealsStmt->execute();
$mealsResult = $mealsStmt->get_result();
$meals = [];
while ($row = $mealsResult->fetch_assoc()) {
    $meals[] = $row;
}

// Fetch recent orders (limited to 10)
$ordersQuery = "SELECT o.order_id, o.total_price, o.order_date, o.ord_type, o.order_status,
                u.u_name as customer_name
                FROM orders o
                JOIN customer c ON o.customer_id = c.user_id
                JOIN users u ON c.user_id = u.user_id
                WHERE o.cloud_kitchen_id = ?
                ORDER BY o.order_date DESC
                LIMIT 10";

$ordersStmt = $conn->prepare($ordersQuery);
$ordersStmt->bind_param("i", $kitchen_id);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();
$orders = [];
while ($row = $ordersResult->fetch_assoc()) {
    $orders[] = $row;
}

// Fetch reviews and ratings
$reviewsQuery = "SELECT r.review_no, r.stars, r.review_date,
                 u.u_name as customer_name
                 FROM reviews r
                 JOIN customer c ON r.customer_id = c.user_id
                 JOIN users u ON c.user_id = u.user_id
                 WHERE r.cloud_kitchen_id = ?
                 ORDER BY r.review_date DESC
                 LIMIT 10";

$reviewsStmt = $conn->prepare($reviewsQuery);
$reviewsStmt->bind_param("i", $kitchen_id);
$reviewsStmt->execute();
$reviewsResult = $reviewsStmt->get_result();
$reviews = [];
while ($row = $reviewsResult->fetch_assoc()) {
    $reviews[] = $row;
}

// Get order statistics
$orderStatsQuery = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                    SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    SUM(total_price) as total_revenue
                    FROM orders
                    WHERE cloud_kitchen_id = ?";

$orderStatsStmt = $conn->prepare($orderStatsQuery);
$orderStatsStmt->bind_param("i", $kitchen_id);
$orderStatsStmt->execute();
$orderStatsResult = $orderStatsStmt->get_result();
$orderStats = $orderStatsResult->fetch_assoc();

// Get complaints
$complaintsQuery = "SELECT id, subject, message, status, created_at
                    FROM complaints
                    WHERE kitchen_id = ?
                    ORDER BY created_at DESC";

$complaintsStmt = $conn->prepare($complaintsQuery);
$complaintsStmt->bind_param("i", $kitchen_id);
$complaintsStmt->execute();
$complaintsResult = $complaintsStmt->get_result();
$complaints = [];
while ($row = $complaintsResult->fetch_assoc()) {
    $complaints[] = $row;
}

// Close all statements
$kitchenStmt->close();
$categoriesStmt->close();
$tagsStmt->close();
$mealsStmt->close();
$ordersStmt->close();
$reviewsStmt->close();
$orderStatsStmt->close();
$complaintsStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($kitchenData['business_name']); ?> - Cloud Kitchen Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="styles1.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--background);
        }
        .header-section {
            background-color:var(--background)
            color: black;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .meal-card {
            transition: transform 0.3s;
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
        }
        .meal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .rating {
            color: #ffc107;
        }
        .tag {
            display: inline-block;
            background-color: #e9ecef;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }
        .stats-card {
            text-align: center;
            padding: 20px;
        }
        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #6c757d;
        }
        .stats-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .section-header {
            border-bottom: 2px solid #e57e24;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #343a40;
        }
        .navbar {
            background-color: #e57e24;
        }
        
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="admin_cloud_kitchen_dashboard.php">
                <i class="fas fa-utensils me-2"></i>Cloud Kitchen Management System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="admin_cloud_kitchen_dashboard.php">
                            <i class="fas fa-chart-line me-1"></i>Admin Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="manage_kitchens.php">
                            <i class="fas fa-cogs me-1"></i>Manage Kitchens
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="kitchen_list.php">
                            <i class="fas fa-store me-1"></i>Public View
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><?php echo htmlspecialchars($kitchenData['business_name']); ?></h1>
                    <div class="rating mb-2">
                        <?php
                        $rating = round($kitchenData['average_rating']);
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        echo " " . number_format($kitchenData['average_rating'], 1);
                        ?>
                    </div>
                    <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($kitchenData['address']); ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge <?php echo $kitchenData['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?> p-2 mb-2">
                        <i class="fas <?php echo $kitchenData['status'] == 'active' ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                        <?php echo ucfirst(htmlspecialchars($kitchenData['status'])); ?>
                    </span>
                    <div class="mt-2">
                        <a href="_kitchens.php" class="btn" style="background-color: #e57e24; color: white;">
                            <i class="fas fa-arrow-left me-2"></i>Back to Management
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card dashboard-card mb-4">
                    <div class="card-body">
                        <h4 class="section-header"><i class="fas fa-info-circle me-2"></i>Kitchen Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-utensils me-2"></i>Speciality:</strong> <?php echo htmlspecialchars($kitchenData['speciality_name']); ?></p>
                                <p><strong><i class="fas fa-clock me-2"></i>Experience:</strong> <?php echo htmlspecialchars($kitchenData['years_of_experience']); ?></p>
                                <p><strong><i class="fas fa-calendar-alt me-2"></i>Since:</strong> <?php echo htmlspecialchars($kitchenData['start_year']); ?></p>
                                <p><strong><i class="fas fa-envelope me-2"></i>Email:</strong> <?php echo htmlspecialchars($kitchenData['mail']); ?></p>
                                <p><strong><i class="fas fa-phone me-2"></i>Phone:</strong> <?php echo htmlspecialchars($kitchenData['phone']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-user me-2"></i>Owner:</strong> <?php echo htmlspecialchars($kitchenData['u_name']); ?></p>
                                <p><strong><i class="fas fa-id-card me-2"></i>National ID:</strong> <?php echo htmlspecialchars($kitchenData['c_n_id']); ?></p>
                                <p><strong><i class="fas fa-map-marker-alt me-2"></i>Zone:</strong> <?php echo htmlspecialchars($kitchenData['zone_name'] ?? 'Not Assigned'); ?></p>
                                <p><strong><i class="fas fa-cogs me-2"></i>Customized Orders:</strong> 
                                    <?php if ($kitchenData['customized_orders']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-times me-1"></i>No</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong><i class="fas fa-shield-alt me-2"></i>Approval Status:</strong> 
                                    <?php if ($kitchenData['is_approved']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pending</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($kitchenData['latitude'] && $kitchenData['longitude']): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong><i class="fas fa-location-arrow me-2"></i>Coordinates:</strong> 
                                    <span class="text-muted">Lat: <?php echo $kitchenData['latitude']; ?>, Lng: <?php echo $kitchenData['longitude']; ?></span>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <h5 class="mt-4 mb-3"><i class="fas fa-tags me-2"></i>Dietary Tags</h5>
                        <div>
                            <?php foreach ($tags as $tag): ?>
                                <span class="tag"><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($tag['tag_name']); ?></span>
                            <?php endforeach; ?>
                            <?php if (empty($tags)): ?>
                                <p class="text-muted">No dietary tags specified</p>
                            <?php endif; ?>
                        </div>

                        <h5 class="mt-4 mb-3"><i class="fas fa-layer-group me-2"></i>Cuisine Categories</h5>
                        <div>
                            <?php foreach ($categories as $category): ?>
                                <span class="tag"><i class="fas fa-utensils me-1"></i><?php echo htmlspecialchars($category['c_name']); ?></span>
                            <?php endforeach; ?>
                            <?php if (empty($categories)): ?>
                                <p class="text-muted">No categories specified</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h4 class="section-header"><i class="fas fa-chart-pie me-2"></i>Performance</h4>
                        <div class="row mt-4">
                            <div class="col-6 text-center">
                                <div class="stats-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stats-value"><?php echo number_format($orderStats['total_orders'] ?? 0); ?></div>
                                <div class="stats-label">Total Orders</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="stats-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stats-value"><?php echo number_format($kitchenData['orders_count']); ?></div>
                                <div class="stats-label">Completed</div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-6 text-center">
                                <div class="stats-icon">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <div class="stats-value"><?php echo number_format($orderStats['delivered_orders'] ?? 0); ?></div>
                                <div class="stats-label">Delivered</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="stats-icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stats-value"><?php echo number_format($orderStats['cancelled_orders'] ?? 0); ?></div>
                                <div class="stats-label">Cancelled</div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top text-center">
                            <div class="stats-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stats-value">$<?php echo number_format($orderStats['total_revenue'] ?? 0, 2); ?></div>
                            <div class="stats-label">Total Revenue</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h4 class="section-header"><i class="fas fa-utensils me-2"></i>Menu Items</h4>
                        <div class="row">
                            <?php if (empty($meals)): ?>
                                <div class="col-12">
                                    <p class="text-center text-muted">No meals available at this time.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($meals as $meal): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 meal-card">
                                            <?php if (!empty($meal['photo'])): ?>
                                                <img src="<?php echo htmlspecialchars($meal['photo']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($meal['name']); ?>">
                                            <?php else: ?>
                                                <div class="bg-light text-center py-5">
                                                    <i class="fas fa-image fa-3x text-muted"></i>
                                                    <p class="mt-2 text-muted">No Image Available</p>
                                                </div>
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($meal['name']); ?></h5>
                                                <p class="card-text text-muted"><?php echo htmlspecialchars($meal['description']); ?></p>
                                                <p class="fw-bold text-primary">$<?php echo number_format($meal['price'], 2); ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge <?php 
                                                        if ($meal['status'] == 'available') echo 'bg-success';
                                                        else if ($meal['status'] == 'low stock') echo 'bg-warning';
                                                        else echo 'bg-danger';
                                                    ?>">
                                                        <i class="fas <?php 
                                                            if ($meal['status'] == 'available') echo 'fa-check-circle';
                                                            else if ($meal['status'] == 'low stock') echo 'fa-exclamation-circle';
                                                            else echo 'fa-times-circle';
                                                        ?> me-1"></i>
                                                        <?php echo ucfirst(htmlspecialchars($meal['status'])); ?>
                                                    </span>
                                                    <span><i class="fas fa-layer-group me-1"></i>Stock: <?php echo $meal['stock_quantity']; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h4 class="section-header"><i class="fas fa-shopping-cart me-2"></i>Recent Orders</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orders)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No orders found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td><?php echo $order['order_id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                <td><span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($order['ord_type'])); ?></span></td>
                                                <td class="text-nowrap">$<?php echo number_format($order['total_price'], 2); ?></td>
                                                <td class="text-nowrap"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        if ($order['order_status'] == 'delivered') echo 'bg-success';
                                                        else if ($order['order_status'] == 'in_progress') echo 'bg-primary';
                                                        else if ($order['order_status'] == 'cancelled') echo 'bg-danger';
                                                        else echo 'bg-warning';
                                                    ?>">
                                                        <i class="fas <?php 
                                                            if ($order['order_status'] == 'delivered') echo 'fa-check-circle';
                                                            else if ($order['order_status'] == 'in_progress') echo 'fa-spinner fa-spin';
                                                            else if ($order['order_status'] == 'cancelled') echo 'fa-times-circle';
                                                            else echo 'fa-clock';
                                                        ?> me-1"></i>
                                                        <?php echo ucfirst(htmlspecialchars($order['order_status'])); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h4 class="section-header"><i class="fas fa-star me-2"></i>Customer Reviews</h4>
                        <?php if (empty($reviews)): ?>
                            <p class="text-center text-muted">No reviews yet.</p>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($review['customer_name']); ?>
                                            </h5>
                                            <div class="rating">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $review['stars']) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <p class="card-text text-muted small mt-2">
                                            <i class="fas fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($review['review_date'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4 mb-5">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h4 class="section-header"><i class="fas fa-exclamation-triangle me-2"></i>Complaints</h4>
                        <?php if (empty($complaints)): ?>
                            <p class="text-center text-muted">No complaints filed.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($complaints as $complaint): ?>
                                            <tr>
                                                <td><?php echo $complaint['id']; ?></td>
                                                <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($complaint['message'], 0, 50)) . (strlen($complaint['message']) > 50 ? '...' : ''); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $complaint['status'] == 'resolved' ? 'bg-success' : 'bg-warning'; ?>">
                                                        <i class="fas <?php echo $complaint['status'] == 'resolved' ? 'fa-check-circle' : 'fa-clock'; ?> me-1"></i>
                                                        <?php echo ucfirst(htmlspecialchars($complaint['status'])); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 