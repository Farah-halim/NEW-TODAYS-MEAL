<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: \NEW-TODAYS-MEAL\Register&Login\login.php");
    exit();
}

require_once '../DB_connection.php';

// Get cloud kitchen ID from session or database
if (!isset($_SESSION['cloud_kitchen_id'])) {
    $stmt = $conn->prepare("SELECT user_id FROM cloud_kitchen_owner WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: unauthorized.php");
        exit();
    }
    
    $row = $result->fetch_assoc();
    $_SESSION['cloud_kitchen_id'] = $row['user_id'];
    $stmt->close();
}

$cloud_kitchen_id = $_SESSION['cloud_kitchen_id'];

// Handle status update requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_status') {
        $order_id = $_POST['order_id'] ?? null;
        $new_status = $_POST['new_status'] ?? null;
        
        if (!$new_status || !$order_id) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }
        
        $valid_statuses = ['preparing', 'ready_for_pickup', 'in_transit'];
        if (!in_array($new_status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }

        // Verify the order belongs to this kitchen
        $verify_stmt = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ? AND cloud_kitchen_id = ?");
        $verify_stmt->bind_param("ii", $order_id, $cloud_kitchen_id);
        $verify_stmt->execute();
        
        if ($verify_stmt->get_result()->num_rows !== 1) {
            echo json_encode(['success' => false, 'message' => 'Order not found or unauthorized']);
            exit;
        }

        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ? AND cloud_kitchen_id = ?");
        $stmt->bind_param("sii", $new_status, $order_id, $cloud_kitchen_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }
}

// Get all orders for this kitchen with proper filtering
$sql = "SELECT o.*, 
               u.u_name AS customer_name,
               cko.business_name AS kitchen_name,
               co.status AS custom_order_status
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        JOIN cloud_kitchen_owner cko ON o.cloud_kitchen_id = cko.user_id
        LEFT JOIN customized_order co ON o.order_id = co.order_id
        WHERE o.cloud_kitchen_id = ? 
        AND (o.ord_type != 'customized' OR (o.ord_type = 'customized' AND co.status = 'accepted' AND co.customer_approval = 'approved'))
        ORDER BY o.order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cloud_kitchen_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
$today = date('Y-m-d');
$total_orders_today = 0;
$pending_orders = 0;
$preparing_orders = 0;
$delivered_orders = 0;
$total_revenue = 0;

// Fetch all orders data and calculate insights in one loop
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
    
    if (date('Y-m-d', strtotime($row['order_date'])) == $today) {
        $total_orders_today++;
    }
    
    if ($row['order_status'] == 'pending') {
        $pending_orders++;
    } elseif ($row['order_status'] == 'preparing') {
        $preparing_orders++;
    } elseif ($row['order_status'] == 'delivered') {
        $delivered_orders++;
    }
    
    $total_revenue += $row['total_price'];
}

// Get all order items and packages only if we have orders
if (!empty($orders)) {
    $order_ids = array_column($orders, 'order_id');
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    
    // Fetch all order items (for normal orders and scheduled all_at_once)
    $sql = "SELECT oc.order_id, m.name AS meal_name, oc.quantity, oc.price 
            FROM order_content oc
            JOIN meals m ON oc.meal_id = m.meal_id
            JOIN orders o ON oc.order_id = o.order_id
            WHERE oc.order_id IN ($placeholders)
            AND (o.ord_type = 'normal' OR (o.ord_type = 'scheduled' AND o.delivery_type = 'all_at_once'))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($order_ids)), ...$order_ids);
    $stmt->execute();
    $items_result = $stmt->get_result();
    $all_items = [];
    
    while ($row = $items_result->fetch_assoc()) {
        $all_items[$row['order_id']][] = $row;
    }

    // Fetch all packages for scheduled daily_delivery orders
    $sql = "SELECT op.order_id, 
                   op.package_id, 
                   op.package_name, 
                   op.delivery_date,  
                   op.package_price,
                   GROUP_CONCAT(CONCAT(mip.quantity, 'x ', m.name, ' - $', mip.price) SEPARATOR '<br>') AS items
            FROM order_packages op
            JOIN meals_in_each_package mip ON op.package_id = mip.package_id
            JOIN meals m ON mip.meal_id = m.meal_id
            JOIN orders o ON op.order_id = o.order_id
            WHERE op.order_id IN ($placeholders)
            AND o.ord_type = 'scheduled' 
            AND o.delivery_type = 'daily_delivery'
            GROUP BY op.package_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($order_ids)), ...$order_ids);
    $stmt->execute();
    $packages_result = $stmt->get_result();
    $all_packages = [];
    
    while ($row = $packages_result->fetch_assoc()) {
        $all_packages[$row['order_id']][] = $row;
    }

    // Get all customized order details (only accepted ones with customer approval)
    $sql = "SELECT * FROM customized_order 
            WHERE order_id IN ($placeholders) AND status = 'accepted' AND customer_approval = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($order_ids)), ...$order_ids);
    $stmt->execute();
    $custom_result = $stmt->get_result();
    $all_custom_details = [];
    while ($row = $custom_result->fetch_assoc()) {
        $all_custom_details[$row['order_id']] = $row;
    }

    // Attach all data to orders
    foreach ($orders as &$order) {
        $order_id = $order['order_id'];
        
        if ($order['ord_type'] == 'normal') {
            $order['items'] = $all_items[$order_id] ?? [];
            $order['packages'] = [];
        } 
        elseif ($order['ord_type'] == 'scheduled') {
            if ($order['delivery_type'] == 'all_at_once') {
                $order['items'] = $all_items[$order_id] ?? [];
                $order['packages'] = [];
            } 
            elseif ($order['delivery_type'] == 'daily_delivery') {
                $order['packages'] = $all_packages[$order_id] ?? [];
                $order['items'] = [];
            }
        }
        elseif ($order['ord_type'] == 'customized') {
            $order['custom_details'] = $all_custom_details[$order_id] ?? null;
            $order['items'] = [];
            $order['packages'] = [];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="front_end/orders/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">
</head>
<body>
<?php include 'global/navbar.php'; ?>

<main class="main-content">
    <h1 class="orders-title">Order Management System</h1>

    <div class="content">
        <div class="filters-wrapper">
            <div class="filters-container">
                <div class="filter-groups">
                    <div class="filter-group">
                        <div class="date-filter">
                            <label>Filter by date:</label>
                            <input type="date" class="date-input" id="dateFilter" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="filter-group">
                            <h3>Order Type</h3>
                            <div class="filter-buttons">
                                <button class="filter-btn active" data-filter="all">All Orders</button>
                                <button class="filter-btn" data-filter="normal">Normal</button>
                                <button class="filter-btn" data-filter="scheduled">Scheduled</button>
                                <button class="filter-btn" data-filter="customized">Customized</button>
                            </div>
                        </div>
                        <div class="filter-group">
                            <h3>Status</h3>
                            <div class="filter-buttons">
                                <div class="filter-buttons">
                                    <button class="filter-btn active" data-status="all">All Status</button>
                                    <button class="filter-btn" data-status="pending">Pending</button>
                                    <button class="filter-btn" data-status="preparing">Preparing</button>
                                    <button class="filter-btn" data-status="ready_for_pickup">Ready</button>
                                    <button class="filter-btn" data-status="in_transit">In Transit</button>
                                    <button class="filter-btn" data-status="delivered">Delivered</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="orders-list">
                <?php foreach ($orders as $order): 
                    $order_id = $order['order_id'];
                    $order_type = $order['ord_type'];
                    $order_status = $order['order_status'];
                    $status_class = str_replace('_', '-', $order_status);
                    $delivery_type = $order['delivery_type'];
                    $order_date = date('Y-m-d', strtotime($order['order_date']));
                    $total_price = $order['total_price'];
                ?>
                <div class="order-card" data-type="<?php echo $order_type; ?>" data-status="<?php echo $status_class; ?>" data-order-id="<?php echo $order_id; ?>" data-order-date="<?php echo $order_date; ?>" data-total-price="<?php echo $total_price; ?>">
                    <div class="order-summary">
                        <div class="order-header">
                            <div class="order-header-left">
                                <h3>#ORD<?php echo str_pad($order_id, 3, '0', STR_PAD_LEFT); ?></h3>
                                <span class="order-type-tag <?php echo $order_type; ?>">
                                    <?php 
                                    if ($order_type == 'scheduled') {
                                        echo 'Scheduled (' . ucfirst(str_replace('_', ' ', $delivery_type)) . ')';
                                    } else {
                                        echo ucfirst($order_type);
                                    }
                                    ?>
                                </span>
                            </div>
                            <span class="order-status <?php echo $status_class; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $order_status)); ?>
                            </span>
                        </div>
                        
                        <?php if ($order_type == 'customized' && isset($order['custom_details'])): ?>
                        <div class="order-info custom-order-info">
                            <div class="info-item">Order Type: <strong><?php echo ucfirst($order_type); ?></strong></div>
                            <div class="info-item">Budget: 
                                <span class="budget-approved">
                                    Approved ($<?php echo $order['custom_details']['chosen_amount']; ?>)
                                </span>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="order-info">
                            <span class="info-item">Items: <?php 
                                if ($order_type == 'scheduled' && $delivery_type == 'daily_delivery') {
                                    echo count($order['packages']);
                                } else {
                                    echo count($order['items']);
                                }
                            ?></span>
                            <span class="info-separator">|</span>
                            <span class="info-item">Total: $<?php echo number_format($total_price, 2); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-details">
                        <div class="order-items">
                            <?php if ($order_type == 'scheduled'): ?>
                                <div class="items-header">
                                    <h4><?php 
                                        if ($delivery_type == 'daily_delivery') {
                                            echo 'Daily Delivery Packages';
                                        } else {
                                            echo 'Order Items (All at Once)';
                                        }
                                    ?></h4>
                                    <select class="status-select" onchange="updateStatus(<?php echo $order_id; ?>, this.value)">
                                        <option value="" selected disabled>Update Status</option>
                                        <option value="preparing" <?php echo ($order_status == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                                        <option value="ready_for_pickup" <?php echo ($order_status == 'ready_for_pickup') ? 'selected' : ''; ?>>Ready</option>
                                        <option value="in_transit" <?php echo ($order_status == 'in_transit') ? 'selected' : ''; ?>>With Delivery</option>
                                    </select>
                                </div>

                                <?php if($delivery_type == 'daily_delivery'): ?>
                                    <?php if (!empty($order['packages'])): ?>
                                    <div class="delivery-packages">
                                        <?php foreach ($order['packages'] as $package): ?>
                                        <div class="package" data-package-id="<?php echo $package['package_id']; ?>">
                                            <div class="package-header">
                                                <span><?php echo $package['package_name']; ?> (<?php echo date('F j, Y', strtotime($package['delivery_date'])); ?>)</span>
                                            </div>
                                            <div class="package-items">
                                                <?php echo $package['items']; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <p>No packages found for this order</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (!empty($order['items'])): ?>
                                    <ul>
                                        <?php foreach ($order['items'] as $item): ?>
                                        <li><?php echo $item['quantity']; ?>x <?php echo $item['meal_name']; ?> - $<?php echo number_format($item['price'], 2); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php else: ?>
                                    <p>No items found for this order</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php elseif ($order_type == 'customized' && isset($order['custom_details'])): ?>
                                <div class="items-header">
                                    <h4>Custom Order Details</h4>
                                    <?php if (!in_array($order_status, ['delivered', 'in_transit'])): ?>
                                        <select class="status-select" onchange="updateStatus(<?php echo $order_id; ?>, this.value)">
                                            <option value="" selected disabled>Update Status</option>
                                            <option value="preparing" <?php echo ($order_status == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                                            <option value="ready_for_pickup" <?php echo ($order_status == 'ready_for_pickup') ? 'selected' : ''; ?>>Ready</option>
                                            <option value="in_transit" <?php echo ($order_status == 'in_transit') ? 'selected' : ''; ?>>With Delivery</option>
                                        </select>
                                    <?php endif; ?>
                                </div>
                                <div class="custom-order-details">
                                    <p><strong>Description:</strong> <?php echo $order['custom_details']['ord_description']; ?></p>
                                    <p><strong>Needed by:</strong> <?php echo date('F j, Y g:i A', strtotime($order['custom_details']['preferred_completion_date'])); ?></p>
                                    <p><strong>Servings:</strong> <?php echo $order['custom_details']['people_servings']; ?> people</p>
                                    <?php if (!empty($order['custom_details']['img_reference'])): ?>
                                        <div class="reference-image-container">
                                            <span class="reference-label">Reference Image:</span>
                                            <img src="../uploads/custom_orders/<?php echo htmlspecialchars(basename($order['custom_details']['img_reference'])); ?>" alt="Reference Image" class="reference-image">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="items-header">
                                    <h4>Order Items</h4>
                                    <select class="status-select" onchange="updateStatus(<?php echo $order_id; ?>, this.value)">
                                        <option value="" selected disabled>Update Status</option>
                                        <option value="preparing" <?php echo ($order_status == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                                        <option value="ready_for_pickup" <?php echo ($order_status == 'ready_for_pickup') ? 'selected' : ''; ?>>Ready</option>
                                        <option value="in_transit" <?php echo ($order_status == 'in_transit') ? 'selected' : ''; ?>>With Delivery</option>
                                    </select>
                                </div>
                                <?php if (!empty($order['items'])): ?>
                                <ul>
                                    <?php foreach ($order['items'] as $item): ?>
                                    <li><?php echo $item['quantity']; ?>x <?php echo $item['meal_name']; ?> - $<?php echo number_format($item['price'], 2); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php else: ?>
                                <p>No items found for this order</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="insights-sidebar">
                <div class="insight-card">
                    <h3 class="insight-title">Total Orders Today</h3>
                    <p class="insight-value" id="totalOrdersToday"><?php echo $total_orders_today; ?></p>
                </div>
                <div class="insight-card">
                    <h3 class="insight-title">Pending Orders</h3>
                    <p class="insight-value" id="pendingOrders"><?php echo $pending_orders; ?></p>
                </div>
                <div class="insight-card">
                    <h3 class="insight-title">Delivered Orders</h3>
                    <p class="insight-value" id="deliveredOrders"><?php echo $delivered_orders; ?></p>
                </div>
                <div class="insight-card">
                    <h3 class="insight-title">Total Revenue</h3>
                    <p class="insight-value" id="totalRevenue">$<?php echo number_format($total_revenue, 2); ?></p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Function to update status via AJAX
function updateStatus(orderId, status) {
    if (!status) return;

    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('new_status', status);
    formData.append('order_id', orderId);

    fetch('orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const orderCard = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
            if (orderCard) {
                // Update the status display
                const statusDisplay = orderCard.querySelector('.order-status');
                const statusClass = status.replace('_', '-');
                statusDisplay.className = 'order-status ' + statusClass;
                
                // Update status text with proper formatting
                let statusText = '';
                switch(status) {
                    case 'ready_for_pickup': statusText = 'READY FOR PICKUP'; break;
                    case 'in_transit': statusText = 'In Transit'; break;
                    default: statusText = status.charAt(0).toUpperCase() + status.slice(1);
                }
                statusDisplay.textContent = statusText;
                
                // Update the data attribute
                orderCard.dataset.status = statusClass;

                // Remove status select dropdown for "With Delivery" and "Delivered" statuses
                if (status === 'in_transit' || status === 'delivered') {
                    const statusSelect = orderCard.querySelector('.status-select');
                    if (statusSelect) {
                        statusSelect.remove();
                    }
                }
                
                // Reapply filters to ensure the order appears in the correct filter
                applyFilters();
            }
        } else {
            alert('Error updating status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
}

// Function to apply all active filters
function applyFilters() {
    const status = document.querySelector('.filter-btn[data-status].active')?.dataset.status || 'all';
    const type = document.querySelector('.filter-btn[data-filter].active')?.dataset.filter || 'all';
    const selectedDate = document.getElementById('dateFilter').value;

    document.querySelectorAll('.order-card').forEach(card => {
        const cardStatus = card.dataset.status;
        const cardType = card.dataset.type;
        const orderDate = card.dataset.orderDate;
        
        // Date filter
        const dateMatch = !selectedDate || orderDate === selectedDate;
        
        // Status filter - handle both hyphen and underscore formats
        const normalizedStatus = cardStatus.replace('-', '_');
        const statusMatch = status === 'all' || 
                  (status === 'pending' && normalizedStatus === 'pending') ||
                  (status === 'preparing' && normalizedStatus === 'preparing') ||
                  (status === 'ready_for_pickup' && normalizedStatus === 'ready_for_pickup') ||
                  (status === 'in_transit' && normalizedStatus === 'in_transit') ||
                  (status === 'delivered' && normalizedStatus === 'delivered');
        
        // Type filter
        const typeMatch = type === 'all' || type === cardType;
        
        card.classList.toggle('hidden', !(statusMatch && typeMatch && dateMatch));
    });
    
    updateInsights();
}

// Function to update insights based on visible orders
function updateInsights() {
    const visibleOrders = document.querySelectorAll('.order-card:not(.hidden)');
    let totalToday = 0;
    let pending = 0;
    let preparing = 0;
    let ready = 0;
    let inTransit = 0;
    let delivered = 0;
    let revenue = 0;
    const today = new Date().toISOString().split('T')[0];

    visibleOrders.forEach(order => {
        const orderDate = order.dataset.orderDate;
        const status = order.dataset.status;
        const totalText = order.querySelector('.info-item:last-child')?.textContent;
        const totalPrice = totalText ? parseFloat(totalText.replace(/[^0-9.]/g, '')) : 0;

        if (orderDate === today) {
            totalToday++;
        }
        
        // Count by status
        if (status === 'pending') {
            pending++;
        }
        if (status === 'preparing') {
            preparing++;
        }
        if (status === 'ready-for-pickup') {
            ready++;
        }
        if (status === 'in-transit') {
            inTransit++;
        }
        if (status === 'delivered') {
            delivered++;
        }
        
        revenue += totalPrice;
    });

    document.getElementById('totalOrdersToday').textContent = totalToday;
    document.getElementById('pendingOrders').textContent = pending;
    document.getElementById('deliveredOrders').textContent = delivered;
    document.getElementById('totalRevenue').textContent = '$' + revenue.toFixed(2);
}

// Simplified filter functions
function filterByDate(selectedDate) {
    applyFilters();
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Set up date filter
    const dateFilter = document.getElementById('dateFilter');
    dateFilter.addEventListener('change', () => filterByDate(dateFilter.value));

    // Toggle order card expansion
    document.querySelectorAll('.order-card').forEach(card => {
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.status-select')) {
                card.classList.toggle('expanded');
            }
        });
    });

    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.parentElement.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });
});
</script>
</body>
</html>