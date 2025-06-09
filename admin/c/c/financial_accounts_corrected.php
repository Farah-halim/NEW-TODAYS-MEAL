<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_dashboard.php');
    exit();
}
require_once 'db_connect.php';

// CORRECTED: Calculate proper financial summaries
$summaryQuery = "SELECT 
    -- CASH ORDERS: Accounts Receivable (Food amount + 10% of delivery fees)
    (SELECT COALESCE(SUM(pd.total_ord_price + (pd.delivery_fees * 0.1)), 0) 
     FROM payment_details pd WHERE pd.p_method = 'cash') as accounts_receivable,
    
    -- ONLINE ORDERS: Delivery Fees Payable (Platform owes full delivery fees)
    (SELECT COALESCE(SUM(pd.delivery_fees), 0) 
     FROM payment_details pd WHERE pd.p_method = 'visa') as delivery_payable,
    
    -- ALL ORDERS: Kitchen Payable (85% of food amount)
    (SELECT COALESCE(SUM(pd.total_ord_price * 0.85), 0) 
     FROM payment_details pd) as kitchen_payable,
    
    -- Platform Revenue: 15% from all food + 10% from cash delivery fees
    (SELECT COALESCE(
        (SELECT SUM(pd.total_ord_price * 0.15) FROM payment_details pd) + 
        (SELECT SUM(pd.delivery_fees * 0.1) FROM payment_details pd WHERE pd.p_method = 'cash'), 0)
    ) as platform_revenue,
    
    -- Order counts
    (SELECT COUNT(*) FROM payment_details WHERE p_method = 'cash') as cash_orders,
    (SELECT COUNT(*) FROM payment_details WHERE p_method = 'visa') as online_orders";

$summaryStmt = $pdo->query($summaryQuery);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

// Calculate net position
$net_position = $summary['accounts_receivable'] - $summary['delivery_payable'] - $summary['kitchen_payable'];

// Get cash orders with CORRECTED calculations
$cashOrdersQuery = "SELECT o.order_id, o.order_date,
                   u.u_name as customer_name, cko.business_name,
                   pd.total_ord_price, pd.delivery_fees, pd.total_payment,
                   -- Platform commissions
                   (pd.total_ord_price * 0.15) as platform_food_commission,
                   (pd.delivery_fees * 0.1) as platform_delivery_commission,
                   -- Total receivable
                   (pd.total_ord_price + (pd.delivery_fees * 0.1)) as amount_owed_to_platform,
                   -- Kitchen amount
                   (pd.total_ord_price * 0.85) as kitchen_amount,
                   -- Delivery keeps
                   (pd.delivery_fees * 0.9) as delivery_company_keeps
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
                     pd.total_ord_price, pd.delivery_fees, pd.total_payment,
                     -- Platform keeps 15% commission from food
                     (pd.total_ord_price * 0.15) as platform_food_commission,
                     -- Kitchen gets 85% of food amount
                     (pd.total_ord_price * 0.85) as kitchen_amount,
                     -- Platform owes full delivery fee to delivery company
                     pd.delivery_fees as delivery_fee_owed
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
                        SUM(pd.total_ord_price * 0.15) as platform_commission,
                        SUM(pd.total_ord_price * 0.85) as net_amount_owed_to_kitchen,
                        AVG(pd.total_ord_price) as avg_order_value
                        FROM cloud_kitchen_owner cko
                        JOIN orders o ON cko.user_id = o.cloud_kitchen_id
                        JOIN payment_details pd ON o.order_id = pd.order_id
                        GROUP BY cko.user_id, cko.business_name
                        ORDER BY net_amount_owed_to_kitchen DESC";

$kitchenPayablesStmt = $pdo->query($kitchenPayablesQuery);
$kitchenPayables = $kitchenPayablesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Accounts - CORRECTED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .financial-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(229, 126, 36, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .financial-card:hover { transform: translateY(-3px); }
        .financial-amount { font-size: 2rem; font-weight: 700; margin: 10px 0; }
        .receivable-amount { color: #28a745; }
        .payable-amount { color: #dc3545; }
        .revenue-amount { color: #007bff; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 style="color: #e57e24; font-weight: 700;">
                <i class="fas fa-chart-pie me-3"></i>Financial Accounts - CORRECTED
            </h1>
        </div>

        <!-- Alert -->
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Data Issue Identified!</h5>
            <p><strong>Problem:</strong> The database has <code>website_revenue</code> as an extra amount, but your business logic requires it to be a 15% commission from food amount.</p>
            <p><strong>This page shows corrected calculations based on your requirements.</strong></p>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="financial-card">
                    <i class="fas fa-hand-holding-usd text-success" style="font-size: 2rem;"></i>
                    <div class="financial-amount receivable-amount">
                        $<?= number_format($summary['accounts_receivable'], 2) ?>
                    </div>
                    <div class="fw-bold">Cash Orders Receivable</div>
                    <small class="text-muted"><?= $summary['cash_orders'] ?> cash orders</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="financial-card">
                    <i class="fas fa-truck text-warning" style="font-size: 2rem;"></i>
                    <div class="financial-amount payable-amount">
                        $<?= number_format($summary['delivery_payable'], 2) ?>
                    </div>
                    <div class="fw-bold">Delivery Payable</div>
                    <small class="text-muted"><?= $summary['online_orders'] ?> online orders</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="financial-card">
                    <i class="fas fa-store text-danger" style="font-size: 2rem;"></i>
                    <div class="financial-amount payable-amount">
                        $<?= number_format($summary['kitchen_payable'], 2) ?>
                    </div>
                    <div class="fw-bold">Kitchen Payable</div>
                    <small class="text-muted">85% of food amounts</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="financial-card">
                    <i class="fas fa-chart-line text-primary" style="font-size: 2rem;"></i>
                    <div class="financial-amount revenue-amount">
                        $<?= number_format($summary['platform_revenue'], 2) ?>
                    </div>
                    <div class="fw-bold">Platform Revenue</div>
                    <small class="text-muted">Net: $<?= number_format($net_position, 2) ?></small>
                </div>
            </div>
        </div>

        <!-- Cash Orders Table -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-money-bill-wave me-2"></i>Cash Orders - Accounts Receivable</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Kitchen</th>
                                <th>Food Amount</th>
                                <th>Delivery Fee</th>
                                <th>Customer Paid</th>
                                <th class="bg-success text-white">RECEIVABLE</th>
                                <th>Kitchen Gets</th>
                                <th>Delivery Keeps</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cashOrders as $order): ?>
                            <tr>
                                <td><strong>#<?= $order['order_id'] ?></strong></td>
                                <td><?= date('M d', strtotime($order['order_date'])) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= htmlspecialchars($order['business_name']) ?></td>
                                <td>$<?= number_format($order['total_ord_price'], 2) ?></td>
                                <td>$<?= number_format($order['delivery_fees'], 2) ?></td>
                                <td><strong>$<?= number_format($order['total_payment'], 2) ?></strong></td>
                                <td><strong class="text-success">$<?= number_format($order['amount_owed_to_platform'], 2) ?></strong></td>
                                <td>$<?= number_format($order['kitchen_amount'], 2) ?></td>
                                <td>$<?= number_format($order['delivery_company_keeps'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Business Logic -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5><i class="fas fa-info-circle me-2"></i>Corrected Business Logic</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success">Cash Orders</h6>
                        <ul>
                            <li>Customer pays delivery person</li>
                            <li>Delivery keeps 90% of delivery fee</li>
                            <li>Platform gets food + 10% delivery commission</li>
                            <li>Kitchen gets 85% of food amount</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning">Online Orders</h6>
                        <ul>
                            <li>Platform collects payment</li>
                            <li>Platform owes delivery company full delivery fee</li>
                            <li>Kitchen gets 85% of food amount</li>
                            <li>Platform keeps 15% food commission</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 