<?php
/**
 * Delivery history page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure user is logged in
require_login();

// Get completed and cancelled deliveries
$completedDeliveries = get_deliveries('completed');
$cancelledDeliveries = get_deliveries('cancelled');

// Include header
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0">Delivery History</h1>
    <a href="deliveries.php" class="d-none d-sm-inline-block btn btn-primary">
        <i class="fas fa-motorcycle fa-sm me-1"></i> View Active Deliveries
    </a>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <!-- Completed Count -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Completed Deliveries
                        </div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo count($completedDeliveries); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cancelled Count -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Cancelled Deliveries
                        </div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo count($cancelledDeliveries); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Distance -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Distance
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            <?php echo number_format(calculate_total_distance($completedDeliveries), 1); ?> km
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-route fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Completion Rate -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Completion Rate
                        </div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 me-3 font-weight-bold">
                                    <?php 
                                    $stats = get_delivery_stats();
                                    echo $stats['completion_rate']; 
                                    ?>%
                                </div>
                            </div>
                            <div class="col">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: <?php echo $stats['completion_rate']; ?>%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percent fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Tabs -->
<ul class="nav nav-tabs mb-4" id="statusTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-tab-pane" type="button" role="tab" aria-selected="true">
            <i class="fas fa-check-circle me-1"></i> Completed
            <span class="badge bg-success ms-1"><?php echo count($completedDeliveries); ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled-tab-pane" type="button" role="tab" aria-selected="false">
            <i class="fas fa-times-circle me-1"></i> Cancelled
            <span class="badge bg-danger ms-1"><?php echo count($cancelledDeliveries); ?></span>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="statusTabsContent">
    <!-- Completed Deliveries Tab -->
    <div class="tab-pane fade show active" id="completed-tab-pane" role="tabpanel" aria-labelledby="completed-tab" tabindex="0">
        <?php if (count($completedDeliveries) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Order #</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Provider</th>
                            <th scope="col">Scheduled</th>
                            <th scope="col">Completed</th>
                            <th scope="col">Distance</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completedDeliveries as $delivery): ?>
                        <tr>
                            <td><?php echo $delivery['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($delivery['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($delivery['provider_name']); ?></td>
                            <td><?php echo format_datetime($delivery['scheduled_time']); ?></td>
                            <td><?php echo format_datetime($delivery['completion_time']); ?></td>
                            <td><?php echo number_format($delivery['distance'], 1); ?> km</td>
                            <td>
                                <a href="delivery-details.php?id=<?php echo $delivery['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No completed deliveries found.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Cancelled Deliveries Tab -->
    <div class="tab-pane fade" id="cancelled-tab-pane" role="tabpanel" aria-labelledby="cancelled-tab" tabindex="0">
        <?php if (count($cancelledDeliveries) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Order #</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Provider</th>
                            <th scope="col">Scheduled</th>
                            <th scope="col">Cancelled</th>
                            <th scope="col">Distance</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cancelledDeliveries as $delivery): ?>
                        <tr>
                            <td><?php echo $delivery['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($delivery['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($delivery['provider_name']); ?></td>
                            <td><?php echo format_datetime($delivery['scheduled_time']); ?></td>
                            <td><?php echo format_datetime($delivery['cancelled_time']); ?></td>
                            <td><?php echo number_format($delivery['distance'], 1); ?> km</td>
                            <td>
                                <a href="delivery-details.php?id=<?php echo $delivery['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No cancelled deliveries found.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';

/**
 * Calculate total distance from a list of deliveries
 * 
 * @param array $deliveries List of deliveries
 * @return float Total distance in kilometers
 */
function calculate_total_distance($deliveries) {
    $totalDistance = 0;
    foreach ($deliveries as $delivery) {
        $totalDistance += $delivery['distance'];
    }
    return $totalDistance;
}
?>