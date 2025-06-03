<?php
/**
 * Active deliveries listing page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure user is logged in
require_login();

// Get pending deliveries
$pendingDeliveries = get_deliveries('pending');

// Get in-progress deliveries
$inProgressDeliveries = get_deliveries('in-progress');

// Get delayed deliveries
$delayedDeliveries = get_deliveries('delayed');

// Include header
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0">Active Deliveries</h1>
    <div class="d-flex">
        <form class="d-none d-sm-inline-block form-inline me-auto me-md-3 my-2 my-md-0" id="deliverySearchForm">
            <div class="input-group">
                <input type="text" class="form-control bg-light border-0 small" placeholder="Search for deliveries..." 
                       aria-label="Search" aria-describedby="basic-addon2" id="searchQuery">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Search Results Container (initially hidden) -->
<div id="searchResultsContainer" style="display: none;">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Search Results</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('searchQuery').value = ''; document.getElementById('searchResultsContainer').style.display = 'none'; document.getElementById('defaultDeliveriesContainer').style.display = 'block';">
                <i class="fas fa-times me-1"></i> Clear
            </button>
        </div>
        <div class="card-body">
            <div id="searchResults"></div>
        </div>
    </div>
</div>

<!-- Default Deliveries Container -->
<div id="defaultDeliveriesContainer">
    <!-- Deliveries Map -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Deliveries Map Overview</h5>
        </div>
        <div class="card-body p-0">
            <div id="deliveries-map" class="map-container"></div>
        </div>
    </div>
    
    <!-- Status Tabs -->
    <ul class="nav nav-tabs mb-4" id="statusTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-tab-pane" type="button" role="tab" aria-selected="true">
                <i class="fas fa-clock me-1"></i> Pending
                <span class="badge bg-secondary ms-1"><?php echo count($pendingDeliveries); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="inprogress-tab" data-bs-toggle="tab" data-bs-target="#inprogress-tab-pane" type="button" role="tab" aria-selected="false">
                <i class="fas fa-truck me-1"></i> In Progress
                <span class="badge bg-primary ms-1"><?php echo count($inProgressDeliveries); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="delayed-tab" data-bs-toggle="tab" data-bs-target="#delayed-tab-pane" type="button" role="tab" aria-selected="false">
                <i class="fas fa-exclamation-triangle me-1"></i> Delayed
                <span class="badge bg-warning ms-1"><?php echo count($delayedDeliveries); ?></span>
            </button>
        </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content" id="statusTabsContent">
        <!-- Pending Deliveries Tab -->
        <div class="tab-pane fade show active" id="pending-tab-pane" role="tabpanel" aria-labelledby="pending-tab" tabindex="0">
            <div class="row">
                <?php if (count($pendingDeliveries) > 0): ?>
                    <?php foreach ($pendingDeliveries as $delivery): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-secondary bg-opacity-10">
                                <h5 class="mb-0">Order #<?php echo $delivery['order_id']; ?></h5>
                                <span class="badge bg-secondary">Pending</span>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-2">Customer: <?php echo htmlspecialchars($delivery['customer_name']); ?></h6>
                                <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($delivery['customer_address']); ?></p>
                                <p class="mb-2"><i class="fas fa-store me-2"></i><?php echo htmlspecialchars($delivery['provider_name']); ?></p>
                                <p class="mb-2"><i class="fas fa-calendar me-2"></i><?php echo format_datetime($delivery['scheduled_time']); ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="delivery-details.php?id=<?php echo $delivery['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-info-circle me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No pending deliveries found.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- In Progress Deliveries Tab -->
        <div class="tab-pane fade" id="inprogress-tab-pane" role="tabpanel" aria-labelledby="inprogress-tab" tabindex="0">
            <div class="row">
                <?php if (count($inProgressDeliveries) > 0): ?>
                    <?php foreach ($inProgressDeliveries as $delivery): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-primary bg-opacity-10">
                                <h5 class="mb-0">Order #<?php echo $delivery['order_id']; ?></h5>
                                <span class="badge bg-primary">In Progress</span>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-2">Customer: <?php echo htmlspecialchars($delivery['customer_name']); ?></h6>
                                <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($delivery['customer_address']); ?></p>
                                <p class="mb-2"><i class="fas fa-store me-2"></i><?php echo htmlspecialchars($delivery['provider_name']); ?></p>
                                <p class="mb-2">
                                    <i class="fas fa-truck-loading me-2"></i> Picked up: <?php echo format_datetime($delivery['pickup_time']); ?>
                                </p>
                            </div>
                            <div class="card-footer">
                                <a href="delivery-details.php?id=<?php echo $delivery['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-info-circle me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No deliveries in progress.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Delayed Deliveries Tab -->
        <div class="tab-pane fade" id="delayed-tab-pane" role="tabpanel" aria-labelledby="delayed-tab" tabindex="0">
            <div class="row">
                <?php if (count($delayedDeliveries) > 0): ?>
                    <?php foreach ($delayedDeliveries as $delivery): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-warning bg-opacity-10">
                                <h5 class="mb-0">Order #<?php echo $delivery['order_id']; ?></h5>
                                <span class="badge bg-warning">Delayed</span>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-2">Customer: <?php echo htmlspecialchars($delivery['customer_name']); ?></h6>
                                <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($delivery['customer_address']); ?></p>
                                <p class="mb-2"><i class="fas fa-store me-2"></i><?php echo htmlspecialchars($delivery['provider_name']); ?></p>
                                <p class="mb-2"><i class="fas fa-calendar me-2"></i><?php echo format_datetime($delivery['scheduled_time']); ?></p>
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i> This delivery is delayed. Please contact the customer to update them on the status.
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="delivery-details.php?id=<?php echo $delivery['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-info-circle me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No delayed deliveries.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>