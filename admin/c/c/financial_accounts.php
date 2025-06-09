<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_dashboard.php');
    exit();
}
require_once 'db_connect.php';

// Get financial summary statistics
$summaryQuery = "SELECT 
    -- Accounts Receivable (Cash orders: food amount + 10% of delivery fees)
    (SELECT COALESCE(SUM(pd.total_ord_price + (pd.delivery_fees * 0.1)), 0) FROM payment_details pd WHERE pd.p_method = 'cash') as accounts_receivable,
    
    -- Delivery Fees Payable (Online orders: platform owes delivery fees)
    (SELECT COALESCE(SUM(pd.delivery_fees), 0) FROM payment_details pd WHERE pd.p_method = 'visa') as delivery_payable,
    
    -- Kitchen Payable (All orders: order amount minus 15% commission)
    (SELECT COALESCE(SUM(pd.total_ord_price * 0.85), 0) FROM payment_details pd) as kitchen_payable,
    
    -- Total orders count
    (SELECT COUNT(*) FROM orders) as total_orders,
    
    -- Total Revenue: 15% commission from food orders + 10% commission from delivery fees
    (SELECT COALESCE(SUM(pd.total_ord_price * 0.15), 0) FROM payment_details pd) as food_commission_revenue,
    (SELECT COALESCE(SUM(pd.delivery_fees * 0.1), 0) FROM payment_details pd) as delivery_commission_revenue";

$summaryStmt = $pdo->query($summaryQuery);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

// Calculate total revenue from commissions
$total_revenue = $summary['food_commission_revenue'] + $summary['delivery_commission_revenue'];

// Get cash orders (receivables)
$cashOrdersQuery = "SELECT o.order_id, o.order_date, o.total_price,
                   u.u_name as customer_name, cko.business_name,
                   pd.total_payment, pd.total_ord_price, pd.delivery_fees,
                   (pd.total_ord_price * 0.15) as website_commission,
                   (pd.total_ord_price * 0.85) as kitchen_amount,
                   (pd.total_ord_price + (pd.delivery_fees * 0.1)) as amount_owed_to_platform,
                   (pd.delivery_fees * 0.1) as platform_delivery_share
                   FROM orders o
                   JOIN payment_details pd ON o.order_id = pd.order_id
                   JOIN customer c ON o.customer_id = c.user_id
                   JOIN users u ON c.user_id = u.user_id
                   JOIN cloud_kitchen_owner cko ON o.cloud_kitchen_id = cko.user_id
                   WHERE pd.p_method = 'cash'
                   ORDER BY o.order_date DESC";

$cashOrdersStmt = $pdo->query($cashOrdersQuery);
$cashOrders = $cashOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get online orders (delivery payables)
$onlineOrdersQuery = "SELECT o.order_id, o.order_date, o.total_price,
                     u.u_name as customer_name, du.u_name as delivery_person,
                     pd.delivery_fees, pd.total_payment
                     FROM orders o
                     JOIN payment_details pd ON o.order_id = pd.order_id
                     JOIN customer c ON o.customer_id = c.user_id
                     JOIN users u ON c.user_id = u.user_id
                     LEFT JOIN deliveries d ON CAST(o.order_id AS CHAR) = d.order_id
                     LEFT JOIN users du ON d.delivery_person_id = du.user_id
                     WHERE pd.p_method = 'visa'
                     ORDER BY o.order_date DESC";

$onlineOrdersStmt = $pdo->query($onlineOrdersQuery);
$onlineOrders = $onlineOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get kitchen payables summary
$kitchenPayablesQuery = "SELECT cko.business_name, cko.user_id,
                        COUNT(o.order_id) as order_count,
                        SUM(pd.total_ord_price) as total_food_amount,
                        SUM(pd.total_ord_price * 0.15) as total_commission,
                        SUM(pd.total_ord_price * 0.85) as net_amount_owed
                        FROM cloud_kitchen_owner cko
                        JOIN orders o ON cko.user_id = o.cloud_kitchen_id
                        JOIN payment_details pd ON o.order_id = pd.order_id
                        GROUP BY cko.user_id, cko.business_name
                        ORDER BY net_amount_owed DESC";

$kitchenPayablesStmt = $pdo->query($kitchenPayablesQuery);
$kitchenPayables = $kitchenPayablesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent settlements
$recentQuery = "SELECT fs.*, o.order_id, o.order_date, o.total_price, 
                cko.business_name, u.u_name as customer_name,
                pd.p_method, pd.total_payment, pd.delivery_fees
                FROM financial_settlements fs
                JOIN orders o ON fs.order_id = o.order_id
                JOIN cloud_kitchen_owner cko ON o.cloud_kitchen_id = cko.user_id
                JOIN customer c ON o.customer_id = c.user_id
                JOIN users u ON c.user_id = u.user_id
                JOIN payment_details pd ON o.order_id = pd.order_id
                ORDER BY fs.created_at DESC
                LIMIT 10";

$recentStmt = $pdo->query($recentQuery);
$recentSettlements = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle settlement actions
if ($_POST['action'] ?? '' === 'mark_settled') {
    $settlement_id = (int)$_POST['settlement_id'];
    $reference = $_POST['reference'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    try {
        $updateQuery = "UPDATE financial_settlements 
                       SET settlement_status = 'settled', 
                           settlement_date = NOW(),
                           settlement_reference = ?,
                           notes = ?
                       WHERE settlement_id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$reference, $notes, $settlement_id]);
        
        $message = "Settlement marked as completed successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error updating settlement: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Accounts Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Enhanced Kitchen Styles -->
    <link rel="stylesheet" href="enhanced-kitchen-styles.css">

    <style>
        /* Financial Accounts Specific Styles */
        .sidebar {
            position: fixed;
            top: 56px;
            left: 0;
            bottom: 0;
            width: 250px;
            overflow-y: auto;
            z-index: 1000;
            
        } 

                                        /* Enhanced Sidebar */
        .sidebar {
                background: linear-gradient(180deg, #fef3dc 0%, #f5e0c2 100%) !important;
                box-shadow: 6px 0 20px rgba(0,0,0,0.12) !important;
                border-right: 1px solid rgba(229, 126, 36, 0.25) !important;
     }  

       .sidebar .nav-link {
           color: #3d6f5d !important;
           border-radius: 10px !important;
           margin: 6px 15px !important;
          padding: 14px 18px !important;
          transition: var(--transition-enhanced) !important;
          font-weight: 500 !important;
          display: flex !important;
          align-items: center !important;
          gap: 12px !important;
       }

        .sidebar .nav-link:hover {
           background: linear-gradient(135deg, #e57e24 0%, #d16919 100%) !important;
           color: white !important;
           transform: translateX(8px) scale(1.02) !important;
           box-shadow: 0 6px 20px rgba(229, 126, 36, 0.4) !important;
     }

        .main-content {
            margin-left: 250px;
            margin-top: 56px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.active {
                left: 0;
            }
        }

        .financial-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(229, 126, 36, 0.1);
            border-radius: 12px;
            margin-bottom: 1rem;
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.08);
            position: relative;
            overflow: hidden;
        }

        .financial-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            transition: width 0.3s ease;
        }

        .financial-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(229, 126, 36, 0.15);
            border-color: rgba(229, 126, 36, 0.3);
        }

        .receivable-card::before {
            background: linear-gradient(45deg, #28a745, #20c997);
        }

        .payable-card::before {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
        }

        .revenue-card::before {
            background: linear-gradient(45deg, #007bff, #6f42c1);
        }

        .net-position-card::before {
            background: linear-gradient(45deg, #e57e24, #ff9948);
        }

        .financial-amount {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .receivable-amount {
            color: #28a745;
        }

        .payable-amount {
            color: #dc3545;
        }

        .revenue-amount {
            color: #007bff;
        }

        .net-amount {
            color: #e57e24;
        }

        .financial-label {
            color: #6c757d;
            font-size: 0.95rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .financial-icon {
            font-size: 3rem;
            opacity: 0.1;
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .tab-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.08);
            margin-top: 2rem;
        }

        .nav-tabs {
            border: none;
            margin-bottom: 0;
        }

        .nav-tabs .nav-link {
            border: 1px solid rgba(229, 126, 36, 0.1);
            border-radius: 12px 12px 0 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            color: #6c757d;
            font-weight: 600;
            padding: 15px 25px;
            margin-right: 5px;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background: linear-gradient(135deg, #e57e24 0%, #ff9948 100%);
            color: white;
            transform: translateY(-2px);
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #e57e24 0%, #ff9948 100%);
            color: white;
            border-color: #e57e24;
        }

        .settlement-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: white;
        }

        .status-settled {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .status-partially-settled {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            padding: 8px 16px;
            font-size: 0.85rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }



        .page-header {
            background: linear-gradient(135deg, #e57e24 0%, #ff9948 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(229, 126, 36, 0.3);
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .header-actions .btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .header-actions .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="bg-light">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-coins me-2"></i>Financial Accounts Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">
                            <i class="fas fa-chart-line me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_categories.php">
                            <i class="fas fa-tags me-1"></i>Categories
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="main-content">
        <!-- Enhanced Page Header -->
        <div class="page-header fade-in">
            <h1><i class="fas fa-coins me-3"></i>Financial Accounts</h1>
            <p>Manage accounts receivable, payable, and settlement tracking</p>
            <div class="header-actions">
                <button class="btn" onclick="exportData()">
                    <i class="fas fa-download me-2"></i>Export Data
                </button>
                <button class="btn" onclick="location.reload()">
                    <i class="fas fa-refresh me-2"></i>Refresh
                </button>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Financial Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="financial-card receivable-card">
                    <i class="fas fa-hand-holding-usd financial-icon"></i>
                    <div class="financial-amount receivable-amount">
                        $<?= number_format($summary['accounts_receivable'], 2) ?>
                    </div>
                    <div class="financial-label">Accounts Receivable</div>
                    <small class="text-muted">Food amount + 10% delivery commission owed to us</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="financial-card payable-card">
                    <i class="fas fa-truck financial-icon"></i>
                    <div class="financial-amount payable-amount">
                        $<?= number_format($summary['delivery_payable'], 2) ?>
                    </div>
                    <div class="financial-label">Delivery Fees Payable</div>
                    <small class="text-muted">Owed to delivery company</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="financial-card payable-card">
                    <i class="fas fa-store financial-icon"></i>
                    <div class="financial-amount payable-amount">
                        $<?= number_format($summary['kitchen_payable'], 2) ?>
                    </div>
                    <div class="financial-label">Kitchen Payable</div>
                    <small class="text-muted">Owed to cloud kitchens</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="financial-card revenue-card">
                    <i class="fas fa-coins financial-icon"></i>
                    <div class="financial-amount revenue-amount">
                        $<?= number_format($total_revenue, 2) ?>
                    </div>
                    <div class="financial-label">Total Revenue</div>
                    <small class="text-muted">15% food commission + 10% delivery commission</small>
                </div>
            </div>
        </div>



        <!-- Tabbed Content -->
        <ul class="nav nav-tabs" id="financialTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="receivable-tab" data-bs-toggle="tab" data-bs-target="#receivable" type="button" role="tab">
                    <i class="fas fa-hand-holding-usd me-2"></i>Accounts Receivable
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="delivery-payable-tab" data-bs-toggle="tab" data-bs-target="#delivery-payable" type="button" role="tab">
                    <i class="fas fa-truck me-2"></i>Delivery Payable
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="kitchen-payable-tab" data-bs-toggle="tab" data-bs-target="#kitchen-payable" type="button" role="tab">
                    <i class="fas fa-store me-2"></i>Kitchen Payable
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settlements-tab" data-bs-toggle="tab" data-bs-target="#settlements" type="button" role="tab">
                    <i class="fas fa-history me-2"></i>Recent Settlements
                </button>
            </li>
        </ul>

        <div class="tab-content" id="financialTabsContent">
            <!-- Accounts Receivable Tab -->
            <div class="tab-pane fade show active" id="receivable" role="tabpanel">
                <div class="p-4">
                    <h5 class="mb-4">
                        <i class="fas fa-hand-holding-usd me-2 text-success"></i>
                        Accounts Receivable - Cash Orders
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Kitchen</th>
                                    <th>Cash Collected</th>
                                    <th>Delivery Fees</th>
                                    <th>Our Delivery Share (10%)</th>
                                    <th>Amount Owed to Us</th>
                                    <th>Kitchen Amount</th>
                                    <th>Our Commission (15%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cashOrders as $order): ?>
                                <tr>
                                    <td><strong>#<?= $order['order_id'] ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($order['business_name']) ?></td>
                                    <td><strong class="text-success">$<?= number_format($order['total_payment'], 2) ?></strong></td>
                                    <td><strong class="text-warning">$<?= number_format($order['delivery_fees'], 2) ?></strong></td>
                                    <td><strong class="text-info">$<?= number_format($order['platform_delivery_share'], 2) ?></strong></td>
                                    <td><strong class="text-primary">$<?= number_format($order['amount_owed_to_platform'], 2) ?></strong></td>
                                    <td><strong class="text-danger">$<?= number_format($order['kitchen_amount'], 2) ?></strong></td>
                                    <td><strong class="text-success">$<?= number_format($order['website_commission'], 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Delivery Payable Tab -->
            <div class="tab-pane fade" id="delivery-payable" role="tabpanel">
                <div class="p-4">
                    <h5 class="mb-4">
                        <i class="fas fa-truck me-2 text-warning"></i>
                        Delivery Fees Payable - Online Orders
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Delivery Person</th>
                                    <th>Delivery Fee</th>
                                    <th>Total Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($onlineOrders as $order): ?>
                                <tr>
                                    <td><strong>#<?= $order['order_id'] ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($order['delivery_person'] ?? 'Not Assigned') ?></td>
                                    <td><strong class="text-warning">$<?= number_format($order['delivery_fees'], 2) ?></strong></td>
                                    <td><strong class="text-success">$<?= number_format($order['total_payment'], 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kitchen Payable Tab -->
            <div class="tab-pane fade" id="kitchen-payable" role="tabpanel">
                <div class="p-4">
                    <h5 class="mb-4">
                        <i class="fas fa-store me-2 text-info"></i>
                        Kitchen Payables - Summary by Kitchen
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kitchen Name</th>
                                    <th>Order Count</th>
                                    <th>Total Food Amount</th>
                                    <th>Commission Deducted</th>
                                    <th>Net Amount Owed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kitchenPayables as $kitchen): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($kitchen['business_name']) ?></strong></td>
                                    <td><span class="badge bg-primary"><?= $kitchen['order_count'] ?></span></td>
                                    <td><strong class="text-success">$<?= number_format($kitchen['total_food_amount'], 2) ?></strong></td>
                                    <td><strong class="text-warning">$<?= number_format($kitchen['total_commission'], 2) ?></strong></td>
                                    <td><strong class="text-danger">$<?= number_format($kitchen['net_amount_owed'], 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Settlements Tab -->
            <div class="tab-pane fade" id="settlements" role="tabpanel">
                <div class="p-4">
                    <h5 class="mb-4">
                        <i class="fas fa-history me-2 text-primary"></i>
                        Recent Settlement Activity
                    </h5>
                    
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Settlement ID</th>
                                    <th>Order ID</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Settlement Date</th>
                                    <th>Reference</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSettlements as $settlement): ?>
                                <tr>
                                    <td><strong>#<?= $settlement['settlement_id'] ?></strong></td>
                                    <td><?= $settlement['order_id'] ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= ucwords(str_replace('_', ' ', $settlement['settlement_type'])) ?>
                                        </span>
                                    </td>
                                    <td><strong>$<?= number_format($settlement['amount'], 2) ?></strong></td>
                                    <td>
                                        <span class="settlement-status status-<?= $settlement['settlement_status'] ?>">
                                            <?= ucwords(str_replace('_', ' ', $settlement['settlement_status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= $settlement['settlement_date'] ? date('M d, Y', strtotime($settlement['settlement_date'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($settlement['settlement_reference'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($settlement['settlement_status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success" onclick="markSettled(<?= $settlement['settlement_id'] ?>)">
                                                <i class="fas fa-check me-1"></i>Mark Settled
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="viewDetails(<?= $settlement['order_id'] ?>)">
                                                <i class="fas fa-eye me-1"></i>View
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Modal -->
    <div class="modal fade" id="settlementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Settlement as Completed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="mark_settled">
                        <input type="hidden" name="settlement_id" id="modalSettlementId">
                        
                        <div class="mb-3">
                            <label class="form-label">Settlement Reference</label>
                            <input type="text" class="form-control" name="reference" placeholder="Transaction/Reference number">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes about the settlement"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Mark as Settled
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function markSettled(settlementId) {
            document.getElementById('modalSettlementId').value = settlementId;
            new bootstrap.Modal(document.getElementById('settlementModal')).show();
        }

        function viewDetails(orderId) {
            // Redirect to order management page filtered by specific order ID
            window.location.href = '../../manage_orders.php?order_id=' + orderId + '&filter=order_id';
        }

        function exportData() {
            const activeTab = document.querySelector('#financialTabs .nav-link.active').id;
            let csvContent = '';
            let filename = '';
            
            switch(activeTab) {
                case 'receivable-tab':
                    csvContent = exportReceivableData();
                    filename = 'accounts_receivable_' + getCurrentDate() + '.csv';
                    break;
                case 'delivery-payable-tab':
                    csvContent = exportDeliveryPayableData();
                    filename = 'delivery_payable_' + getCurrentDate() + '.csv';
                    break;
                case 'kitchen-payable-tab':
                    csvContent = exportKitchenPayableData();
                    filename = 'kitchen_payable_' + getCurrentDate() + '.csv';
                    break;
                case 'settlements-tab':
                    csvContent = exportSettlementsData();
                    filename = 'settlements_' + getCurrentDate() + '.csv';
                    break;
                default:
                    // Export summary data
                    csvContent = exportSummaryData();
                    filename = 'financial_summary_' + getCurrentDate() + '.csv';
            }
            
            downloadCSV(csvContent, filename);
        }

        function exportReceivableData() {
            const table = document.querySelector('#receivable table');
            return tableToCSV(table);
        }

        function exportDeliveryPayableData() {
            const table = document.querySelector('#delivery-payable table');
            return tableToCSV(table);
        }

        function exportKitchenPayableData() {
            const table = document.querySelector('#kitchen-payable table');
            return tableToCSV(table);
        }

        function exportSettlementsData() {
            const table = document.querySelector('#settlements table');
            return tableToCSV(table);
        }

        function exportSummaryData() {
            let csv = 'Financial Summary Report\n';
            csv += 'Generated on: ' + new Date().toLocaleDateString() + '\n\n';
            csv += 'Account Type,Amount\n';
            
            const receivableAmount = document.querySelector('.receivable-amount').textContent.trim();
            const deliveryPayableAmount = document.querySelector('.payable-amount').textContent.trim();
            const kitchenPayableAmount = document.querySelectorAll('.payable-amount')[1].textContent.trim();
            const revenueAmount = document.querySelector('.revenue-amount').textContent.trim();
            
            csv += 'Accounts Receivable,' + receivableAmount + '\n';
            csv += 'Delivery Fees Payable,' + deliveryPayableAmount + '\n';
            csv += 'Kitchen Payable,' + kitchenPayableAmount + '\n';
            csv += 'Total Revenue (Commissions),' + revenueAmount + '\n';
            
            return csv;
        }

        function tableToCSV(table) {
            let csv = '';
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cols = row.querySelectorAll('td, th');
                let rowData = [];
                
                for (let j = 0; j < cols.length; j++) {
                    let cellData = cols[j].textContent.trim().replace(/"/g, '""');
                    // Remove extra whitespace and newlines
                    cellData = cellData.replace(/\s+/g, ' ');
                    rowData.push('"' + cellData + '"');
                }
                
                csv += rowData.join(',') + '\n';
            }
            
            return csv;
        }

        function downloadCSV(csvContent, filename) {
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        function getCurrentDate() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        }



        // Load dynamic content for tabs
        document.addEventListener('DOMContentLoaded', function() {
            loadReceivableData();
            loadDeliveryPayableData();
            loadKitchenPayableData();
        });

        function loadReceivableData() {
            // AJAX call to load receivable data
            // This will be implemented in the AJAX handler
        }

        function loadDeliveryPayableData() {
            // AJAX call to load delivery payable data
        }

        function loadKitchenPayableData() {
            // AJAX call to load kitchen payable data
        }

        // Add fade-in animation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.page-header').classList.add('fade-in');
        });
    </script>
</body>
</html> 