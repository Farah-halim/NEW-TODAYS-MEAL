<?php
/**
 * Dashboard page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure user is logged in
require_login();

// Get user data
$user = get_logged_in_user();

// Get delivery statistics
$stats = get_delivery_stats();

// Get today's deliveries (scheduled for today)
$todayDeliveries = get_todays_deliveries();

// Get ongoing deliveries (pending or in-progress)
$ongoingDeliveries = get_ongoing_deliveries();

// Include header
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0">Dashboard</h1>
    <a href="deliveries.php" class="d-none d-sm-inline-block btn btn-primary">
        <i class="fas fa-motorcycle fa-sm me-1"></i> View All Deliveries
    </a>
</div>

<!-- Greeting card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                <i class="fas fa-user fa-lg"></i>
            </div>
            <div>
                <h5 class="mb-1">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h5>
                <p class="mb-0 text-muted"><?php echo date('l, F j, Y'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Today's Deliveries
                        </div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['deliveries_today']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Completed Deliveries
                        </div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['status_counts']['completed']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Deliveries
                        </div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['status_counts']['pending']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Completion Rate
                        </div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 me-3 font-weight-bold"><?php echo $stats['completion_rate']; ?>%</div>
                            </div>
                            <div class="col">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $stats['completion_rate']; ?>%"
                                        aria-valuenow="<?php echo $stats['completion_rate']; ?>" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Deliveries Map -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Delivery Map</h5>
            </div>
            <div class="card-body p-0">
                <div id="deliveries-map" class="map-container"></div>
            </div>
        </div>
    </div>
    
    <!-- Today's Deliveries -->
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Today's Deliveries</h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshDeliveryMap()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (count($todayDeliveries) > 0): ?>
                        <?php foreach ($todayDeliveries as $delivery): ?>
                        <a href="delivery-details.php?id=<?php echo $delivery['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start p-3">
                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-<?php echo get_status_class($delivery['status']); ?> me-2"><?php echo ucfirst($delivery['status']); ?></span>
                                    <h6 class="mb-0">Order #<?php echo $delivery['order_id']; ?></h6>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($delivery['customer_name']); ?></p>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i> <?php echo format_time($delivery['scheduled_time']); ?>
                                </small>
                            </div>
                            <div class="ms-2">
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 text-center">
                            <p class="mb-0 text-muted">No deliveries scheduled for today.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ongoing Deliveries -->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Active Deliveries</h5>
            </div>
            <div class="card-body">
                <?php if (count($ongoingDeliveries) > 0): ?>
                <div class="row">
                    <?php foreach ($ongoingDeliveries as $delivery): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-<?php echo get_status_class($delivery['status']); ?> bg-opacity-10">
                                <h5 class="mb-0">Order #<?php echo $delivery['order_id']; ?></h5>
                                <span class="badge bg-<?php echo get_status_class($delivery['status']); ?>"><?php echo ucfirst($delivery['status']); ?></span>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-2">Customer: <?php echo htmlspecialchars($delivery['customer_name']); ?></h6>
                                <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($delivery['customer_address']); ?></p>
                                <p class="mb-2"><i class="fas fa-store me-2"></i><?php echo htmlspecialchars($delivery['provider_name']); ?></p>
                                <p class="mb-2"><i class="fas fa-calendar me-2"></i><?php echo format_datetime($delivery['scheduled_time']); ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="delivery-details.php?id=<?php echo $delivery['id']; ?>" class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No active deliveries at the moment.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';

/**
 * Get deliveries scheduled for today
 * 
 * @return array Array of today's deliveries
 */
function get_todays_deliveries() {
    global $db;
    
    // Get today's date
    $today = date('Y-m-d');
    
    $sql = "
        SELECT d.*, 
               c.name AS customer_name, c.address AS customer_address,
               p.name AS provider_name
        FROM deliveries d
        JOIN customers c ON d.customer_id = c.id
        JOIN food_providers p ON d.provider_id = p.id
        WHERE d.delivery_person_id = :user_id
        AND DATE(d.scheduled_time) = :today
        ORDER BY d.scheduled_time ASC
    ";
    
    $params = [
        ':user_id' => $_SESSION['user_id'],
        ':today' => $today
    ];
    
    return db_query($sql, $params);
}

/**
 * Get ongoing deliveries (pending or in-progress)
 * 
 * @return array Array of ongoing deliveries
 */
function get_ongoing_deliveries() {
    global $db;
    
    $sql = "
        SELECT d.*, 
               c.name AS customer_name, c.address AS customer_address,
               p.name AS provider_name
        FROM deliveries d
        JOIN customers c ON d.customer_id = c.id
        JOIN food_providers p ON d.provider_id = p.id
        WHERE d.delivery_person_id = :user_id
        AND (d.status = 'pending' OR d.status = 'in-progress' OR d.status = 'delayed')
        ORDER BY d.scheduled_time ASC
    ";
    
    $params = [':user_id' => $_SESSION['user_id']];
    
    return db_query($sql, $params);
}

/**
 * Format timestamp to time only
 * 
 * @param string $datetime Datetime string
 * @return string Formatted time
 */
function format_time($datetime) {
    $timestamp = strtotime($datetime);
    return date('g:i A', $timestamp);
}
?>