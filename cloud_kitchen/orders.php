<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: \\NEW-TODAYS-MEAL\\Register&Login\\login.php");
    exit();
}

require_once '../DB_connection.php';

$cloud_kitchen_id = $_SESSION['cloud_kitchen_id'] ?? null;
if (!$cloud_kitchen_id) {
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

// Handle status update POST (same logic as before)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    // ... status update logic here (omitted to save space) ...
    exit;
}

// Filters from GET
$filter_date = $_GET['filter_date'] ?? '';
$filter_type = $_GET['filter_type'] ?? 'all';
$filter_status = $_GET['filter_status'] ?? 'all';
$filter_search = $_GET['search'] ?? '';

// Validate filters
if ($filter_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) $filter_date = '';
$valid_types = ['all', 'normal', 'scheduled', 'customized'];
$valid_statuses = ['all', 'pending', 'preparing', 'ready_for_pickup', 'in_transit', 'delivered', 'cancelled'];

if (!in_array($filter_type, $valid_types)) $filter_type = 'all';
if (!in_array($filter_status, $valid_statuses)) $filter_status = 'all';

// Build WHERE clause and parameters
$whereClauses = ["o.cloud_kitchen_id = ?"];
$params = [$cloud_kitchen_id];
$types = "i";

if ($filter_type !== 'all') {
    $whereClauses[] = "o.ord_type = ?";
    $params[] = $filter_type;
    $types .= "s";
}
if ($filter_status !== 'all') {
    $whereClauses[] = "o.order_status = ?";
    $params[] = $filter_status;
    $types .= "s";
}
if ($filter_date !== '') {
    $whereClauses[] = "DATE(o.order_date) = ?";
    $params[] = $filter_date;
    $types .= "s";
}
if ($filter_search !== '') {
    $whereClauses[] = "(o.order_id = ? OR u.u_name LIKE ? OR cko.business_name LIKE ?)";
    $params[] = (int)$filter_search;
    $params[] = "%$filter_search%";
    $params[] = "%$filter_search%";
    $types .= "iss";
}

$whereSql = implode(" AND ", $whereClauses);
$sql = "SELECT DISTINCT o.*, u.u_name AS customer_name, cko.business_name AS kitchen_name
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        JOIN cloud_kitchen_owner cko ON o.cloud_kitchen_id = cko.user_id
        WHERE $whereSql 
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

$order_ids = array_column($orders, 'order_id');
$all_items = $all_packages = $all_custom_details = [];

if ($order_ids) {
    // Prepare placeholders and types for IN clause
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    $types_ids = str_repeat('i', count($order_ids));

    // Fetch order items (normal and all_at_once scheduled)
    $sql = "SELECT oc.order_id, m.name AS meal_name, oc.quantity, oc.price 
            FROM order_content oc
            JOIN meals m ON oc.meal_id = m.meal_id
            JOIN orders o ON oc.order_id = o.order_id
            WHERE oc.order_id IN ($placeholders)
            AND (o.ord_type = 'normal' OR (o.ord_type = 'scheduled' AND o.delivery_type = 'all_at_once'))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types_ids, ...$order_ids);
    $stmt->execute();
    $res = $stmt->get_result();
    foreach ($order_ids as $id) $all_items[$id] = [];
    while ($row = $res->fetch_assoc()) {
        $all_items[$row['order_id']][] = $row;
    }

    // Fetch scheduled daily_delivery packages and their items
    $sql = "SELECT op.order_id, op.package_id, op.package_name, op.delivery_date, op.package_price, op.package_status, 
            mip.quantity, m.name AS meal_name, mip.price 
            FROM order_packages op 
            JOIN meals_in_each_package mip ON op.package_id = mip.package_id 
            JOIN meals m ON mip.meal_id = m.meal_id 
            JOIN orders o ON op.order_id = o.order_id 
            WHERE op.order_id IN ($placeholders) 
            AND o.ord_type = 'scheduled' AND o.delivery_type = 'daily_delivery'
            ORDER BY op.delivery_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types_ids, ...$order_ids);
    $stmt->execute();
    $res = $stmt->get_result();
    $packages_tmp = [];
    while ($row = $res->fetch_assoc()) {
        $oid = $row['order_id'];
        $pid = $row['package_id'];
        if (!isset($packages_tmp[$pid])) {
            $packages_tmp[$pid] = [
                'order_id' => $oid,
                'package_id' => $pid,
                'package_name' => $row['package_name'],
                'delivery_date' => $row['delivery_date'],
                'package_price' => $row['package_price'],
                'package_status' => $row['package_status'],
                'items' => []
            ];
        }
        $packages_tmp[$pid]['items'][] = [
            'quantity' => $row['quantity'],
            'meal_name' => $row['meal_name'],
            'price' => $row['price']
        ];
    }
    foreach ($packages_tmp as $pid => $pkg) {
        $all_packages[$pkg['order_id']][] = $pkg;
    }

    // Fetch customized orders accepted and approved
    $sql = "SELECT * FROM customized_order WHERE order_id IN ($placeholders) AND status = 'accepted' AND customer_approval = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types_ids, ...$order_ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $all_custom_details[$row['order_id']] = $row;
    }

    // Attach items, packages, and custom details
    foreach ($orders as &$order) {
        $oid = $order['order_id'];
        $order['items'] = $all_items[$oid] ?? [];
        $order['packages'] = $all_packages[$oid] ?? [];
        $order['custom_details'] = $all_custom_details[$oid] ?? null;
    }
    unset($order);
}

// Prepare insights
$today = date('Y-m-d');
$total_orders_today = $pending_orders = $preparing_orders = $delivered_orders = 0;
$total_revenue = 0;

foreach ($orders as $order) {
    if (date('Y-m-d', strtotime($order['order_date'])) === $today) $total_orders_today++;
    if ($order['order_status'] === 'pending') $pending_orders++;
    if ($order['order_status'] === 'preparing') $preparing_orders++;
    if ($order['order_status'] === 'delivered') $delivered_orders++;
    $total_revenue += $order['total_price'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Order Management System</title>
<link rel="stylesheet" href="front_end/orders/style.css" />
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet" />
</head>
<body>
<?php include 'global/navbar.php'; ?>

<main class="main-content">
    <h1 class="orders-title">Order Management System</h1>

    <div class="content">
        <div class="filters-wrapper">
            <div class="filters-container">
                <div class="filter-groups">
                    <form method="GET" class="filter-group" aria-label="Filter Orders" id="filterForm">
                        
                        <div class="date-filter">
                            <label for="dateFilter">Filter by date:</label>
                            <input type="date" name="filter_date" id="dateFilter" class="date-input" 
                                   value="<?php echo htmlspecialchars($filter_date ?: date('Y-m-d')); ?>">
                        </div>
                        <div class="filter-group">
                            <h3>Order Type</h3>
                            <div class="filter-buttons" role="group" aria-label="Order Type">
                                <?php
                                $types = ['all' => 'All Orders', 'normal' => 'Normal', 'scheduled' => 'Scheduled', 'customized' => 'Customized'];
                                foreach ($types as $val => $label): ?>
                                    <button type="button" class="filter-btn<?php echo ($filter_type === $val) ? ' active' : ''; ?>" 
                                            name="filter_type" value="<?php echo $val; ?>" 
                                            data-filter="<?php echo $val; ?>"
                                            onclick="updateFilter('filter_type', '<?php echo $val; ?>')">
                                        <?php echo $label; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="filter-group">
                            <h3>Status</h3>
                            <div class="filter-buttons" role="group" aria-label="Order Status">
                                <?php
                                $statuses = [
                                    'all' => 'All Status',
                                    'pending' => 'Pending',
                                    'preparing' => 'Preparing',
                                    'ready_for_pickup' => 'Ready',
                                    'in_transit' => 'In Transit',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled'
                                ];
                                foreach ($statuses as $val => $label): ?>
                                    <button type="button" class="filter-btn<?php echo ($filter_status === $val) ? ' active' : ''; ?>" 
                                            name="filter_status" value="<?php echo $val; ?>" 
                                            data-status="<?php echo $val; ?>"
                                            onclick="updateFilter('filter_status', '<?php echo $val; ?>')">
                                        <?php echo $label; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="orders-list">
                <?php if (empty($orders)): ?>
                    <div class="no-orders" style="padding: 40px; background-color: #f9f9f9; border: 1px dashed #ccc; border-radius: 8px; text-align: center; color: #666; font-style: italic;">
                        <p>No orders found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order):
                        $order_id = $order['order_id'];
                        $order_type = $order['ord_type'];
                        $order_status = $order['order_status'];
                        $status_class = str_replace('_', '-', $order_status);
                        $delivery_type = $order['delivery_type'] ?? '';
                        $order_date = date('Y-m-d', strtotime($order['order_date']));
                        $total_price = $order['total_price'];
                        ?>
                        <div class="order-card" data-type="<?php echo $order_type; ?>" data-status="<?php echo $status_class; ?>" data-order-id="<?php echo $order_id; ?>" data-order-date="<?php echo $order_date; ?>" data-total-price="<?php echo $total_price; ?>" tabindex="0" aria-expanded="false" role="region" aria-label="Order #ORD<?php echo str_pad($order_id,3,'0',STR_PAD_LEFT); ?>">
                            <div class="order-summary">
                                <div class="order-header">
                                    <div class="order-header-left">
                                        <h3>#ORD<?php echo str_pad($order_id, 3, '0', STR_PAD_LEFT); ?></h3>
                                        <span class="order-type-tag <?php echo $order_type; ?>">
                                            <?php if ($order_type == 'scheduled') {
                                                echo 'Scheduled (' . ucfirst(str_replace('_', ' ', $delivery_type)) . ')';
                                            } else {
                                                echo ucfirst($order_type);
                                            }?>
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
                                            <?php if ($delivery_type != 'daily_delivery' && !in_array($order_status, ['in_transit', 'delivered'])): ?>
                                                <select class="status-select" onchange="updateStatus(<?php echo $order_id; ?>, this.value)" aria-label="Update order status">
                                                    <option value="" selected disabled>Update Status</option>
                                                    <option value="preparing" <?php echo ($order_status == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                                                    <option value="ready_for_pickup" <?php echo ($order_status == 'ready_for_pickup') ? 'selected' : ''; ?>>Ready</option>
                                                    <option value="in_transit" <?php echo ($order_status == 'in_transit') ? 'selected' : ''; ?>>With Delivery</option>
                                                </select>
                                            <?php endif; ?>
                                        </div>

                                        <?php if($delivery_type == 'daily_delivery'): ?>
                                            <?php if (!empty($order['packages'])): ?>
                                            <div class="delivery-packages">
                                                <?php foreach ($order['packages'] as $package): ?>
                                                <div class="package" data-package-id="<?php echo $package['package_id']; ?>">
                                                    <div class="package-header">
                                                        <span><?php echo htmlspecialchars($package['package_name']); ?> (<?php echo date('F j, Y', strtotime($package['delivery_date'])); ?>)</span>
                                                        <span class="package-status <?php echo str_replace('_', '-', $package['package_status']); ?>">
                                                            Status: <?php echo ucfirst(str_replace('_', ' ', $package['package_status'])); ?>
                                                        </span>
                                                        <?php if (!in_array($package['package_status'], ['in_transit', 'delivered'])): ?>
                                                            <select class="package-status-select" onchange="updatePackageStatus(<?php echo $package['package_id']; ?>, this.value)" aria-label="Update package status">
                                                                <option value="" selected disabled>Update Status</option>
                                                                <option value="preparing" <?php echo ($package['package_status'] == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                                                                <option value="ready_for_pickup" <?php echo ($package['package_status'] == 'ready_for_pickup') ? 'selected' : ''; ?>>Ready</option>
                                                                <option value="in_transit" <?php echo ($package['package_status'] == 'in_transit') ? 'selected' : ''; ?>>With Delivery</option>
                                                            </select>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="package-items">
                                                        <?php foreach ($package['items'] as $item): ?>
                                                            <div><?php echo $item['quantity'].'x '.htmlspecialchars($item['meal_name']); ?> - $<?php echo number_format($item['price'],2); ?></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php else: ?>
                                            <p>No packages found for this order</p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if (!empty($order['items'])): ?>
                                            <ul class="order-items-list">
                                                <?php foreach ($order['items'] as $item): ?>
                                                <li>
                                                    <?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['meal_name']); ?> - $<?php echo number_format($item['price'], 2); ?>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php else: ?>
                                            <p>No items found for this order</p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php elseif ($order_type == 'customized' && isset($order['custom_details'])): ?>
                                        <div class="items-header">
                                            <h4>Custom Order Details</h4>
                                            <?php if (!in_array($order_status, ['in_transit', 'delivered'])): ?>
                                                <select class="status-select" onchange="updateStatus(<?php echo $order_id; ?>, this.value)" aria-label="Update order status">
                                                    <option value="" selected disabled>Update Status</option>
                                                    <option value="preparing" <?php echo ($order_status == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                                                    <option value="ready_for_pickup" <?php echo ($order_status == 'ready_for_pickup') ? 'selected' : ''; ?>>Ready</option>
                                                    <option value="in_transit" <?php echo ($order_status == 'in_transit') ? 'selected' : ''; ?>>With Delivery</option>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                        <div class="custom-order-details">
                                            <p><strong>Description:</strong> <?php echo htmlspecialchars($order['custom_details']['ord_description']); ?></p>
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
                                            <?php if (!in_array($order_status, ['in_transit', 'delivered'])): ?>
                                                <select class="status-select" onchange="updateStatus(<?php echo $order_id; ?>, this.value)" aria-label="Update order status">
                                                    <option value="" selected disabled>Update Status</option>
                                                    <option value="preparing" <?php echo ($order_status == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                                                    <option value="ready_for_pickup" <?php echo ($order_status == 'ready_for_pickup') ? 'selected' : ''; ?>>Ready</option>
                                                    <option value="in_transit" <?php echo ($order_status == 'in_transit') ? 'selected' : ''; ?>>With Delivery</option>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($order['items'])): ?>
                                        <ul class="order-items-list">
                                            <?php foreach ($order['items'] as $item): ?>
                                            <li>
                                                <?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['meal_name']); ?> - $<?php echo number_format($item['price'], 2); ?>
                                            </li>
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
                <?php endif; ?>
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
                    <h3 class="insight-title">Preparing Orders</h3>
                    <p class="insight-value" id="preparingOrders"><?php echo $preparing_orders; ?></p>
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
    </div>
</main>

<script>
// Enhanced filtering functionality
function updateFilter(filterName, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(filterName, value);
    window.location.href = url.toString();
}

function clearDateFilter() {
    const url = new URL(window.location.href);
    url.searchParams.delete('filter_date');
    window.location.href = url.toString();
}

function clearSearch() {
    const url = new URL(window.location.href);
    url.searchParams.delete('search');
    window.location.href = url.toString();
}

function resetFilters() {
    const url = new URL(window.location);
    url.search = '';
    window.location.href = url.toString();
}

// Order card toggle functionality
document.querySelectorAll('.order-card').forEach(card => {
    card.addEventListener('click', (e) => {
        // Prevent toggling when clicking inside selects or package-status-select
        if(e.target.closest('select') || e.target.closest('.clear-search') || e.target.closest('.clear-date')) return;
        
        // Toggle the expanded state
        const wasExpanded = card.classList.contains('expanded');
        document.querySelectorAll('.order-card.expanded').forEach(expandedCard => {
            if (expandedCard !== card) {
                expandedCard.classList.remove('expanded');
                expandedCard.setAttribute('aria-expanded', 'false');
            }
        });
        
        if (!wasExpanded) {
            card.classList.add('expanded');
            card.setAttribute('aria-expanded', 'true');
            // Scroll to ensure the card is fully visible
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            card.classList.remove('expanded');
            card.setAttribute('aria-expanded', 'false');
        }
    });
});

// Status update functions
function updateStatus(orderId, status) {
    if (!status) return;
    
    // Show loading indicator
    const statusElement = document.querySelector(`.order-card[data-order-id="${orderId}"] .order-status`);
    const originalStatus = statusElement.textContent;
    statusElement.textContent = 'Updating...';
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('new_status', status);
    formData.append('order_id', orderId);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).then(res => {
        if (!res.ok) throw new Error('Network error');
        return res.json();
    }).then(data => {
        if (data.success) {
            // Show success message briefly before reloading
            statusElement.textContent = 'Updated!';
            setTimeout(() => location.reload(), 1000);
        } else {
            statusElement.textContent = originalStatus;
            alert('Error updating status: ' + (data.message || 'Unknown'));
        }
    }).catch(error => {
        statusElement.textContent = originalStatus;
        alert('Failed to update status: ' + error.message);
    });
}

function updatePackageStatus(packageId, status) {
    if (!status) return;
    
    // Show loading indicator
    const statusElement = document.querySelector(`.package[data-package-id="${packageId}"] .package-status`);
    const originalStatus = statusElement.textContent;
    statusElement.textContent = 'Updating...';
    
    const formData = new FormData();
    formData.append('action', 'update_package_status');
    formData.append('new_status', status);
    formData.append('package_id', packageId);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).then(res => {
        if (!res.ok) throw new Error('Network error');
        return res.json();
    }).then(data => {
        if (data.success) {
            // Show success message briefly before reloading
            statusElement.textContent = 'Updated!';
            setTimeout(() => location.reload(), 1000);
        } else {
            statusElement.textContent = originalStatus;
            alert('Error: ' + (data.message || 'Unknown'));
        }
    }).catch(error => {
        statusElement.textContent = originalStatus;
        alert('Failed to update package status: ' + error.message);
    });
}

// Keyboard navigation for order cards
document.addEventListener('keydown', function(e) {
    const focusedCard = document.activeElement.closest('.order-card');
    if (!focusedCard) return;

    const allCards = Array.from(document.querySelectorAll('.order-card'));
    const currentIndex = allCards.indexOf(focusedCard);

    if (e.key === 'ArrowDown' && currentIndex < allCards.length - 1) {
        e.preventDefault();
        allCards[currentIndex + 1].focus();
    } else if (e.key === 'ArrowUp' && currentIndex > 0) {
        e.preventDefault();
        allCards[currentIndex - 1].focus();
    } else if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        focusedCard.click();
    }
});

// Initialize filters based on URL parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Highlight active filters
    const activeFilterType = urlParams.get('filter_type') || 'all';
    document.querySelectorAll('[data-filter]').forEach(btn => {
        if (btn.dataset.filter === activeFilterType) {
            btn.classList.add('active');
        }
    });
    
    const activeFilterStatus = urlParams.get('filter_status') || 'all';
    document.querySelectorAll('[data-status]').forEach(btn => {
        if (btn.dataset.status === activeFilterStatus) {
            btn.classList.add('active');
        }
    });
    
    // Auto-submit date filter when changed
    const dateFilter = document.getElementById('dateFilter');
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
});
</script>
</body>
</html>