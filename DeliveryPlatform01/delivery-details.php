<?php
/**
 * Delivery details page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure user is logged in
require_login();

// Get delivery ID from request
$deliveryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no ID provided
if (empty($deliveryId)) {
    redirect('deliveries.php');
}

// Get delivery details
$delivery = get_delivery_with_details($deliveryId);

// Check if delivery exists and belongs to the current user
if (!$delivery || $delivery['delivery_person_id'] != $_SESSION['user_id']) {
    set_flash_message('error', 'Delivery not found or access denied');
    redirect('deliveries.php');
}

// Get delivery status history
$statusHistory = get_delivery_status_history($deliveryId);

// Include header
include 'includes/header.php';

// Get status class
$statusClass = get_status_class($delivery['status']);
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0">Delivery #<?php echo $delivery['order_id']; ?></h1>
        <p class="mb-0 text-muted">
            <i class="fas fa-calendar me-1"></i> <?php echo format_datetime($delivery['scheduled_time']); ?>
        </p>
    </div>
    <a href="deliveries.php" class="d-none d-sm-inline-block btn btn-secondary">
        <i class="fas fa-arrow-left fa-sm me-1"></i> Back to Deliveries
    </a>
</div>

<!-- Status and Actions Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="card-title">Current Status</h5>
                <div class="d-flex align-items-center">
                    <span class="badge bg-<?php echo $statusClass; ?> p-2 me-2">
                        <?php echo ucfirst($delivery['status']); ?>
                    </span>
                    <span class="text-muted">
                        <?php 
                        $lastUpdated = !empty($delivery['last_status_update']) ? 
                            'Updated ' . time_elapsed_string($delivery['last_status_update']) : 
                            '';
                        echo $lastUpdated;
                        ?>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <h5 class="card-title">Actions</h5>
                <div class="btn-group">
                    <?php if ($delivery['status'] === 'pending'): ?>
                    <button type="button" class="btn btn-primary btn-update-status" 
                            data-delivery-id="<?php echo $delivery['id']; ?>" 
                            data-status="in-progress" 
                            data-current-status="<?php echo $delivery['status']; ?>">
                        <i class="fas fa-truck-loading me-1"></i> Mark as Picked Up
                    </button>
                    <button type="button" class="btn btn-warning btn-update-status" 
                            data-delivery-id="<?php echo $delivery['id']; ?>" 
                            data-status="delayed" 
                            data-current-status="<?php echo $delivery['status']; ?>">
                        <i class="fas fa-clock me-1"></i> Mark as Delayed
                    </button>
                    <?php elseif ($delivery['status'] === 'in-progress'): ?>
                    <button type="button" class="btn btn-success btn-update-status" 
                            data-delivery-id="<?php echo $delivery['id']; ?>" 
                            data-status="completed" 
                            data-current-status="<?php echo $delivery['status']; ?>">
                        <i class="fas fa-check me-1"></i> Mark as Delivered
                    </button>
                    <button type="button" class="btn btn-warning btn-update-status" 
                            data-delivery-id="<?php echo $delivery['id']; ?>" 
                            data-status="delayed" 
                            data-current-status="<?php echo $delivery['status']; ?>">
                        <i class="fas fa-clock me-1"></i> Mark as Delayed
                    </button>
                    <?php elseif ($delivery['status'] === 'delayed'): ?>
                    <button type="button" class="btn btn-primary btn-update-status" 
                            data-delivery-id="<?php echo $delivery['id']; ?>" 
                            data-status="in-progress" 
                            data-current-status="<?php echo $delivery['status']; ?>">
                        <i class="fas fa-truck me-1"></i> Continue Delivery
                    </button>
                    <button type="button" class="btn btn-success btn-update-status" 
                            data-delivery-id="<?php echo $delivery['id']; ?>" 
                            data-status="completed" 
                            data-current-status="<?php echo $delivery['status']; ?>">
                        <i class="fas fa-check me-1"></i> Mark as Delivered
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($delivery['status'] !== 'completed' && $delivery['status'] !== 'cancelled'): ?>
                    <button type="button" class="btn btn-danger btn-update-status" 
                            data-delivery-id="<?php echo $delivery['id']; ?>" 
                            data-status="cancelled" 
                            data-current-status="<?php echo $delivery['status']; ?>">
                        <i class="fas fa-times me-1"></i> Cancel Delivery
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Delivery Info -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Delivery Information</h5>
            </div>
            <div class="card-body">
                <!-- Customer Information -->
                <div class="mb-4">
                    <h6 class="card-subtitle mb-2 text-muted">Customer</h6>
                    <p class="card-text mb-1">
                        <strong><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($delivery['customer_name']); ?></strong>
                    </p>
                    <p class="card-text mb-1">
                        <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($delivery['customer_address']); ?>
                    </p>
                    <p class="card-text mb-1">
                        <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($delivery['customer_phone']); ?>
                    </p>
                    <p class="card-text mb-1">
                        <i class="fas fa-money-bill me-2"></i><?php echo number_format($delivery['price'], 2) . ' EGP'; ?>
                    </p>
                </div>
                
                <!-- Provider Information -->
                <div class="mb-4">
                    <h6 class="card-subtitle mb-2 text-muted">Food Provider</h6>
                    <p class="card-text mb-1">
                        <strong><i class="fas fa-store me-2"></i><?php echo htmlspecialchars($delivery['provider_name']); ?></strong>
                    </p>
                    <p class="card-text mb-1">
                        <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($delivery['provider_address']); ?>
                    </p>
                    <p class="card-text mb-1">
                        <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($delivery['provider_phone']); ?>
                    </p>
                </div>
                
                <!-- Order Details -->
                <div>
                    <h6 class="card-subtitle mb-2 text-muted">Order Details</h6>
                    <table class="table table-striped table-sm">
                        <tbody>
                            <tr>
                                <td>Order ID</td>
                                <td><?php echo $delivery['order_id']; ?></td>
                            </tr>
                            <tr>
                                <td>Scheduled Time</td>
                                <td><?php echo format_datetime($delivery['scheduled_time']); ?></td>
                            </tr>
                            <tr>
                                <td>Distance</td>
                                <td><?php echo number_format($delivery['distance'], 1); ?> km</td>
                            </tr>
                            <tr>
                                <td>Estimated Duration</td>
                                <td><?php echo $delivery['estimated_duration']; ?> min</td>
                            </tr>
                            <tr>
                                <td>Notes</td>
                                <td><?php echo !empty($delivery['notes']) ? htmlspecialchars($delivery['notes']) : 'No notes provided'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Map and Timeline -->
    <div class="col-lg-6 mb-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Delivery Route</h5>
            </div>
            <div class="card-body p-0">
                <div id="route-map" class="map-container" 
                     data-provider-lat="<?php echo $delivery['provider_latitude']; ?>" 
                     data-provider-lng="<?php echo $delivery['provider_longitude']; ?>" 
                     data-customer-lat="<?php echo $delivery['customer_latitude']; ?>" 
                     data-customer-lng="<?php echo $delivery['customer_longitude']; ?>"
                     data-status="<?php echo $delivery['status']; ?>">
                </div>
            </div>
        </div>
        
        <!-- Status Timeline -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Status Timeline</h5>
            </div>
            <div class="card-body">
                <div class="delivery-timeline">
                    <?php foreach ($statusHistory as $history): ?>
                    <div class="timeline-item">
                        <div class="timeline-icon bg-<?php echo get_status_class($history['status']); ?>">
                            <i class="fas <?php echo get_status_icon($history['status']); ?>"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Status: <?php echo ucfirst($history['status']); ?></h6>
                            <p class="mb-0 text-muted">
                                <i class="far fa-clock me-1"></i> <?php echo format_datetime($history['timestamp']); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($statusHistory)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No status updates yet.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Confirmation Modal -->
<div class="modal fade" id="statusConfirmModal" tabindex="-1" aria-labelledby="statusConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusConfirmModalLabel">Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to update the status of this delivery?</p>
                <input type="hidden" id="confirm-delivery-id" value="">
                <input type="hidden" id="confirm-status" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusUpdate">Confirm</button>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';

/**
 * Get icon for delivery status
 * 
 * @param string $status Status value
 * @return string CSS class for Font Awesome icon
 */
function get_status_icon($status) {
    switch ($status) {
        case 'pending':
            return 'fa-clock';
        case 'in-progress':
            return 'fa-truck';
        case 'completed':
            return 'fa-check';
        case 'cancelled':
            return 'fa-times';
        case 'delayed':
            return 'fa-exclamation-triangle';
        default:
            return 'fa-info-circle';
    }
}

/**
 * Format time elapsed since a timestamp
 * 
 * @param string $datetime Datetime string
 * @return string Formatted time ago string
 */
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d >= 1) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h >= 1) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i >= 1) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'just now';
    }
}
?>