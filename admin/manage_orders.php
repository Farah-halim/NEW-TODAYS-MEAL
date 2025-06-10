<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
require_once 'config.php';

// Check for URL parameters for filtering
$filter_order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;
$filter_type = isset($_GET['filter']) ? $_GET['filter'] : null;

// Build the query with optional filtering
$where_clause = "";
$params = [];
$param_types = "";

if ($filter_order_id && $filter_type === 'order_id') {
    $where_clause = "WHERE o.order_id = ?";
    $params[] = $filter_order_id;
    $param_types .= "i";
}

// Fetch orders with related information
$query = "SELECT o.*, c.u_name as customer_name, cko.business_name as kitchen_name,
          dd.d_status as delivery_status, u.u_name as delivery_man_name
          FROM orders o
          LEFT JOIN customer cu ON o.customer_id = cu.user_id
          LEFT JOIN users c ON cu.user_id = c.user_id
          LEFT JOIN cloud_kitchen_owner cko ON o.cloud_kitchen_id = cko.user_id
          LEFT JOIN delivery_details dd ON o.order_id = dd.ord_id
          LEFT JOIN users u ON dd.user_id = u.user_id
          $where_clause
          ORDER BY o.order_date DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

// Get order counts for different statuses  
$pending_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status='pending'")->fetch_assoc()['count'];
$preparing_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status='preparing'")->fetch_assoc()['count'];
$ready_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status='ready_for_pickup'")->fetch_assoc()['count'];
$in_transit_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status='in_transit'")->fetch_assoc()['count'];
$delivered_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status='delivered'")->fetch_assoc()['count'];
$cancelled_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status='cancelled'")->fetch_assoc()['count'];

// Get delivery personnel list for assignment
$delivery_personnel = $conn->query("SELECT u.user_id, u.u_name FROM users u 
                                  JOIN delivery_man dm ON u.user_id = dm.user_id 
                                  WHERE dm.status = 'online' AND dm.current_status = 'free'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Today's Meal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="styles1.css" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #e57e24;
            --secondary-orange: #f39c12;
            --light-orange: #fff7e5;
            --dark-green: #3d6f5d;
            --light-green: #f0f8f5;
            --warm-brown: #6a4125;
            --cream: #f5e0c2;
            --light-cream: #fef9f0;
        }

        body { 
            background-color: var(--light-orange);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--warm-brown);
        }

        .navbar { 
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            padding: 1rem 0;
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.3);
            z-index: 1050;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: white !important;
        }

        /* Enhanced Sidebar */
        .sidebar {
            position: fixed;
            top: 56px;
            left: 0;
            height: calc(100vh - 56px); 
            width: 250px;
            background: linear-gradient(180deg, #fef3dc 0%, #f5e0c2 100%);
            padding-top: 1rem;
            overflow-y: auto;
            z-index: 1000;
            border-right: 1px solid rgba(229, 126, 36, 0.2);
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: #3d6f5d !important;
            border-radius: 8px;
            margin: 4px 12px;
            padding: 12px 16px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background: linear-gradient(135deg, #fff7e5 0%, #e57e24 100%);
            color:white;
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.3);
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #e57e24 0%, #f39c12 100%);
            color: #6a4125 !important;
            font-weight: 600;
        }

        .sidebar .nav-link.active:hover {
            color: #6a4125 !important;
        }

        .sidebar-heading {
            color: #8b5e3c;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 1rem 0 0.5rem 1rem;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            margin-top: 56px;
            min-height: calc(100vh - 56px);
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
                transition: left 0.3s ease;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .sidebar.active {
                left: 0;
            }
        }

        /* Enhanced Cards */
        .order-card, .card {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(61, 111, 93, 0.1);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-orange);
        }

        .order-card:hover, .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(61, 111, 93, 0.15);
        }

        .order-details {
            display: none;
            background: linear-gradient(135deg, var(--light-cream), #fff);
            border-radius: 0 0 15px 15px;
            border-top: 2px solid var(--cream);
        }

        /* Enhanced Status Pills */
        .status-pill {
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-pending {
            background: linear-gradient(135deg, #ffd700, #ffed4a);
            color: var(--warm-brown);
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }

        .status-preparing {
            background: linear-gradient(135deg, #ff9800, #ffb74d);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
        }

        .status-ready_for_pickup, .status-ready-for-pickup {
            background: linear-gradient(135deg, #2196f3, #64b5f6);
            color: white;
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
        }

        .status-in_transit, .status-in-transit {
            background: linear-gradient(135deg, #9c27b0, #ba68c8);
            color: white;
            box-shadow: 0 2px 8px rgba(156, 39, 176, 0.3);
        }

        .status-delivered {
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            color: white;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }

        .status-cancelled {
            background: linear-gradient(135deg, #f44336, #ef5350);
            color: white;
            box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
        }

        .order-type-badge {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--cream), #fff);
            color: var(--warm-brown);
            border: 1px solid rgba(229, 126, 36, 0.2);
        }

        /* Enhanced Summary Cards */
        .order-summary-cards .card {
            background: linear-gradient(135deg, #dab98b 0%, #e57e24 100%);
            color:rgb(255, 255, 255);
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(196, 142, 47, 0.98);
            transition: all 0.3s ease;
            border-left: none;
        }

        .order-summary-cards .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 30px rgba(218, 185, 139, 0.4);
        }

        .order-summary-cards .card-body {
            padding: 1.8rem;
        }

        .order-summary-cards .card i {
            background: rgba(106, 65, 37, 0.2);
            padding: 15px;
            border-radius: 50%;
            margin-bottom: 15px;
            color:rgb(255, 255, 254);
        }

        /* Enhanced Table */
        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(61, 111, 93, 0.1);
        }

        .table th {
            background: linear-gradient(135deg, var(--cream), #fff);
            color: var(--warm-brown);
            font-weight: 700;
            border: none;
            padding: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 1rem;
            border-color: rgba(229, 126, 36, 0.1);
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(229, 126, 36, 0.05), rgba(243, 156, 18, 0.05));
            transform: scale(1.01);
        }

        /* Enhanced Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--dark-green), #4a7c59);
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2c5547, var(--dark-green));
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(61, 111, 93, 0.3);
        }

        .btn-accent {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-accent:hover {
            background: linear-gradient(135deg, var(--secondary-orange), var(--primary-orange));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.3);
            color: white;
        }

        /* Enhanced Search Bar */
        .input-group {
            box-shadow: 0 4px 15px rgba(61, 111, 93, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        .form-control {
            border: 2px solid var(--cream);
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border-radius: 0;
        }

        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(229, 126, 36, 0.25);
        }

        .input-group-text {
            background: linear-gradient(135deg, var(--cream), #fff);
            border: 2px solid var(--cream);
            color: var(--warm-brown);
            font-weight: 600;
        }

        /* Enhanced Alert */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            border-left: 4px solid;
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(32, 201, 151, 0.1));
            border-left-color: #17a2b8;
            color: var(--warm-brown);
        }

        /* Sidebar Enhancement */
        .sidebar .alert {
            margin: 1rem 15px;
            background: linear-gradient(135deg, rgba(229, 126, 36, 0.1), rgba(243, 156, 18, 0.1));
            border: 1px solid rgba(229, 126, 36, 0.2);
            border-left: 4px solid var(--primary-orange);
        }

        .sidebar .alert-heading {
            color: var(--primary-orange);
            font-weight: 700;
        }

        /* Enhanced Page Header */
        .border-bottom {
            border-bottom: 3px solid var(--cream) !important;
            padding-bottom: 1rem !important;
            margin-bottom: 2rem !important;
        }

        .h2 {
            color: var(--warm-brown);
            font-weight: 700;
            font-size: 2rem;
        }

        /* Button Group Enhancement */
        .btn-group .btn {
            border-radius: 8px;
            margin-right: 5px;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            border: none;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268, #6c757d);
            transform: translateY(-2px);
        }

        /* Logo Styling */
        .sidebar img {
            transition: all 0.3s ease;
        }

        .sidebar img:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark mb-4 sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-shopping-cart me-2"></i>Orders Management System
            </span>
            <div>
                <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a href="c/c/admin_dashboard.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <?php include 'c/c/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            
            <main class="px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Orders Management System</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-accent" onclick="refreshOrders()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="exportOrdersData()">
                                <i class="fas fa-file-export me-1"></i> Export
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>

                <?php if ($filter_order_id && $filter_type === 'order_id'): ?>
                <!-- Filter Indicator -->
                <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-filter me-2"></i>
                    <strong>Filtered View:</strong> Showing details for Order #<?php echo $filter_order_id; ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-sm btn-outline-info ms-3">
                        <i class="fas fa-times me-1"></i> Clear Filter & View All Orders
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'"></button>
                </div>
                <?php endif; ?>

                <!-- Orders Summary Cards -->
                <div class="row order-summary-cards mb-4">
                    <div class="col-md-2">
                        <div class="card text-center py-3">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h2><?php echo $pending_count; ?></h2>
                                <h5>Pending</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center py-3">
                            <div class="card-body">
                                <i class="fas fa-motorcycle fa-2x mb-2"></i>
                                <h2><?php echo $preparing_count; ?></h2>
                                <h5>Preparing</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center py-3">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h2><?php echo $ready_count; ?></h2>
                                <h5>Ready</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center py-3">
                            <div class="card-body">
                                <i class="fas fa-shipping-fast fa-2x mb-2"></i>
                                <h2><?php echo $in_transit_count; ?></h2>
                                <h5>In Transit</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center py-3">
                            <div class="card-body">
                                <i class="fas fa-truck fa-2x mb-2"></i>
                                <h2><?php echo $delivered_count; ?></h2>
                                <h5>Delivered</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center py-3">
                            <div class="card-body">
                                <i class="fas fa-times-circle fa-2x mb-2"></i>
                                <h2><?php echo $cancelled_count; ?></h2>
                                <h5>Cancelled</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-text" id="search-addon">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="orderSearch" placeholder="Search orders by ID, customer, kitchen..." aria-label="Search">
                            <button class="btn btn-accent" type="button">Search</button>
                        </div>
                    </div>
                </div>

                <!-- Orders Content -->
                <div class="tab-content" id="ordersTabContent">
                    <!-- All Orders Tab -->
                    <div class="tab-pane fade show active" id="all-orders" role="tabpanel" aria-labelledby="all-orders-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Date</th>
                                                <th>Customer</th>
                                                <th>Kitchen</th>
                                                <th>Type</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Delivery</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ordersTableBody">
                                            <?php if ($result && $result->num_rows > 0) : ?>
                                                <?php while ($order = $result->fetch_assoc()) : ?>
                                                    <tr class="order-row" data-order-id="<?php echo $order['order_id']; ?>">
                                                        <td>#<?php echo $order['order_id']; ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                        <td><?php echo $order['customer_name'] ?? 'Unknown'; ?></td>
                                                        <td><?php echo $order['kitchen_name'] ?? 'Unknown'; ?></td>
                                                        <td>
                                                            <span class="order-type-badge">
                                                                <?php echo ucfirst($order['ord_type']); ?>
                                                            </span>
                                                        </td>
                                                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                                        <td>
                                                            <span class="status-pill status-<?php echo $order['order_status']; ?>">
                                                                <?php echo ucfirst($order['order_status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($order['delivery_status'])): ?>
                                                                <span class="badge bg-<?php echo $order['delivery_status'] == 'delivered' ? 'success' : 'warning'; ?>">
                                                                    <?php echo ucfirst($order['delivery_status']); ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Not Assigned</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button class="btn btn-sm btn-accent" onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-secondary" onclick="assignDelivery(<?php echo $order['order_id']; ?>)">
                                                                    <i class="fas fa-truck"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <div class="empty-state">
                                                            <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                                                            <h5>No orders found</h5>
                                                            <p class="text-muted">Orders will appear here once they are placed</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other tabs would be populated dynamically based on order type -->
                    <div class="tab-pane fade" id="normal-orders" role="tabpanel" aria-labelledby="normal-orders-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <!-- Will be populated via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="customized-orders" role="tabpanel" aria-labelledby="customized-orders-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <!-- Will be populated via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="scheduled-orders" role="tabpanel" aria-labelledby="scheduled-orders-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <!-- Will be populated via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--accent); color: white;">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Will be filled with AJAX response -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading order details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printOrderBtn">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>



    <!-- Assign Delivery Modal -->
    <div class="modal fade" id="assignDeliveryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--accent); color: white;">
                    <h5 class="modal-title">Assign Delivery Personnel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assignDeliveryForm">
                        <input type="hidden" id="deliveryOrderId" name="order_id">
                        
                        <div class="mb-3">
                            <label for="deliveryPerson" class="form-label">Select Delivery Person</label>
                            <select class="form-select" id="deliveryPerson" name="delivery_person_id" required>
                                <option value="">Select Delivery Person</option>
                                <?php if ($delivery_personnel && $delivery_personnel->num_rows > 0) : ?>
                                    <?php while ($person = $delivery_personnel->fetch_assoc()) : ?>
                                        <option value="<?php echo $person['user_id']; ?>"><?php echo $person['u_name']; ?></option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="" disabled>No delivery personnel available</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deliveryDate" class="form-label">Delivery Date & Time</label>
                            <input type="datetime-local" class="form-control" id="deliveryDate" name="delivery_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deliveryNotes" class="form-label">Delivery Instructions</label>
                            <textarea class="form-control" id="deliveryNotes" name="delivery_notes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveDeliveryAssignmentBtn">Assign</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--secondary); color: var(--dark-brown);">
                    <h5 class="modal-title">Filter Orders</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="mb-3">
                            <label for="dateRange" class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="startDate" name="start_date">
                                <span class="input-group-text">to</span>
                                <input type="date" class="form-control" id="endDate" name="end_date">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="preparing">Preparing</option>
                                <option value="ready_for_pickup">Ready for Pickup</option>
                                <option value="in_transit">In Transit</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="orderTypeFilter" class="form-label">Order Type</label>
                            <select class="form-select" id="orderTypeFilter" name="order_type">
                                <option value="">All Types</option>
                                <option value="normal">Normal</option>
                                <option value="customized">Customized</option>
                                <option value="scheduled">Scheduled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="priceRangeFilter" class="form-label">Price Range</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="minPrice" name="min_price" placeholder="Min">
                                <span class="input-group-text">to</span>
                                <input type="number" class="form-control" id="maxPrice" name="max_price" placeholder="Max">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-secondary" id="resetFilterBtn">Reset</button>
                    <button type="button" class="btn btn-primary" id="applyFilterBtn">Apply Filter</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript for Orders Management -->
    <script>
        // Initialize Bootstrap tabs explicitly and add direct filtering handlers
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tabs
            var tabElements = document.querySelectorAll('#ordersTabs button[data-bs-toggle="tab"]');
            tabElements.forEach(function(tabEl) {
                new bootstrap.Tab(tabEl);
                
                tabEl.addEventListener('shown.bs.tab', function(event) {
                    const targetId = event.target.getAttribute('data-bs-target').replace('#', '');
                    const type = targetId.replace('-orders', '');
                    console.log('Tab shown:', type);
                    window.filterOrdersByType(type === 'all' ? 'all' : type);
                });
            });
            
            // Get tab elements
            const allOrdersTab = document.getElementById('all-orders-tab');
            const normalOrdersTab = document.getElementById('normal-orders-tab');
            const customizedOrdersTab = document.getElementById('customized-orders-tab');
            const scheduledOrdersTab = document.getElementById('scheduled-orders-tab');
            
            // Add direct click handlers for filtering
            if (allOrdersTab) {
                allOrdersTab.addEventListener('click', function() {
                    console.log('All orders tab clicked directly');
                    // Allow Bootstrap to handle tab display
                    setTimeout(function() {
                        window.filterOrdersByType('all');
                    }, 100);
                });
            }
            
            if (normalOrdersTab) {
                normalOrdersTab.addEventListener('click', function() {
                    console.log('Normal orders tab clicked directly');
                    // Allow Bootstrap to handle tab display
                    setTimeout(function() {
                        window.filterOrdersByType('normal');
                    }, 100);
                });
            }
            
            if (customizedOrdersTab) {
                customizedOrdersTab.addEventListener('click', function() {
                    console.log('Customized orders tab clicked directly');
                    // Allow Bootstrap to handle tab display
                    setTimeout(function() {
                        window.filterOrdersByType('customized');
                    }, 100);
                });
            }
            
            if (scheduledOrdersTab) {
                scheduledOrdersTab.addEventListener('click', function() {
                    console.log('Scheduled orders tab clicked directly');
                    // Allow Bootstrap to handle tab display
                    setTimeout(function() {
                        window.filterOrdersByType('scheduled');
                    }, 100);
                });
            }
            
            console.log('Tab click handlers added');

            // Mobile sidebar sync with navbar collapse
            const sidebar = document.querySelector('.sidebar');
            const navbarCollapse = document.getElementById('navbarNav');
    
            // Sync sidebar with navbar collapse
            if (navbarCollapse) {
                navbarCollapse.addEventListener('show.bs.collapse', () => {
                    sidebar.classList.add('active');
                });
    
                navbarCollapse.addEventListener('hide.bs.collapse', () => {
                    sidebar.classList.remove('active');
                });
            }
        });
    </script>
    <script src="orders.js"></script>
</body>
</html> 