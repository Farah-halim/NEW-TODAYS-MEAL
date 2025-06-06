<?php
session_start();
require_once '../DB_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: \NEW-TODAYS-MEAL\Register&Login\login.php");
    exit();
}

// Get user role and details
$user_id = $_SESSION['user_id'];
$query = "SELECT u.u_role, eu.ext_role, cko.user_id AS cloud_kitchen_id 
          FROM users u
          LEFT JOIN external_user eu ON u.user_id = eu.user_id
          LEFT JOIN cloud_kitchen_owner cko ON eu.user_id = cko.user_id
          WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if user is a cloud kitchen owner
if (!$user || $user['u_role'] != 'external_user' || $user['ext_role'] != 'cloud_kitchen_owner' || !$user['cloud_kitchen_id']) {
    header("Location: unauthorized.php");
    exit();
}

// Store cloud kitchen ID in session for future use
$_SESSION['cloud_kitchen_id'] = $user['cloud_kitchen_id'];

// Get today's date for filtering orders
$today = date('Y-m-d');

// Get latest 5 orders for this cloud kitchen that have payment details
$orders_query = "SELECT o.order_id, pd.total_ord_price AS total_price, o.ord_type, o.order_date, o.order_status, 
                        o.delivery_type, c.u_name AS customer_name, co.ord_description AS custom_description
                 FROM orders o
                 JOIN customer cust ON o.customer_id = cust.user_id
                 JOIN users c ON cust.user_id = c.user_id
                 LEFT JOIN customized_order co ON o.order_id = co.order_id
                 JOIN payment_details pd ON o.order_id = pd.order_id  -- Changed from LEFT JOIN to JOIN to ensure payment exists
                 WHERE o.cloud_kitchen_id = ?
                 ORDER BY o.order_date DESC
                 LIMIT 5";
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $user['cloud_kitchen_id']);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);

// Get order items for each order
foreach ($orders as &$order) {
    if ($order['ord_type'] == 'customized') {
        // For customized orders, use the description as the "item"
        $order['items'] = [['name' => 'Custom Order', 'quantity' => 1, 'description' => $order['custom_description']]];
    } 
    elseif ($order['delivery_type'] == 'daily_delivery') {
        // For scheduled orders with daily delivery, get packages and their meals
        $packages_query = "SELECT op.package_id, op.package_name
                           FROM order_packages op
                           WHERE op.order_id = ?";
        $packages_stmt = $conn->prepare($packages_query);
        $packages_stmt->bind_param("i", $order['order_id']);
        $packages_stmt->execute();
        $packages_result = $packages_stmt->get_result();
        $packages = $packages_result->fetch_all(MYSQLI_ASSOC);
        
        $order['items'] = [];
        foreach ($packages as $package) {
            $package_items_query = "SELECT m.name, mip.quantity
                                   FROM meals_in_each_package mip
                                   JOIN meals m ON mip.meal_id = m.meal_id
                                   WHERE mip.package_id = ?";
            $package_items_stmt = $conn->prepare($package_items_query);
            $package_items_stmt->bind_param("i", $package['package_id']);
            $package_items_stmt->execute();
            $package_items_result = $package_items_stmt->get_result();
            $package_items = $package_items_result->fetch_all(MYSQLI_ASSOC);
            
            foreach ($package_items as $item) {
                $order['items'][] = $item;
            }
        }
    } else {
        // For regular orders, get items from order_content
        $items_query = "SELECT m.name, oc.quantity, oc.price
                        FROM order_content oc
                        JOIN meals m ON oc.meal_id = m.meal_id
                        WHERE oc.order_id = ?";
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("i", $order['order_id']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        $order['items'] = $items_result->fetch_all(MYSQLI_ASSOC);
    }
}
unset($order);

// Calculate today's revenue and order counts (only for orders with payment details)
$stats_query = "SELECT 
                SUM(CASE WHEN DATE(o.order_date) = ? THEN pd.total_ord_price ELSE 0 END) AS today_revenue,
                COUNT(CASE WHEN DATE(o.order_date) = ? THEN 1 ELSE NULL END) AS today_orders,
                COUNT(CASE WHEN DATE(o.order_date) = ? AND o.order_status = 'delivered' THEN 1 ELSE NULL END) AS completed_orders,
                COUNT(CASE WHEN DATE(o.order_date) = ? AND o.order_status IN ('pending', 'preparing', 'ready_for_pickup', 'in_transit') THEN 1 ELSE NULL END) AS pending_orders,
                (SELECT COUNT(*) FROM meals WHERE cloud_kitchen_id = ? AND status = 'out of stock') AS out_of_stock
                FROM orders o
                JOIN payment_details pd ON o.order_id = pd.order_id  -- Changed from LEFT JOIN to JOIN to ensure payment exists
                WHERE o.cloud_kitchen_id = ?";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("ssssii", $today, $today, $today, $today, $user['cloud_kitchen_id'], $user['cloud_kitchen_id']);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="front_end/dashboard/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'global/navbar.php'; ?>
    <div class="container">
          <main class="main-content">
              <div class="stats-grid">
                  <div class="stat-card">
                      <div class="stat-content">
                          <div class="stat-info">
                              <p class="stat-label">Today's Revenue</p>
                              <p class="stat-value"><span>$</span><?php echo number_format($stats['today_revenue'] ?? 0, 2); ?></p>
                          </div>
                          <div class="stat-icon revenue"></div>
                      </div>
                      <div class="stat-footer">
                          <img src="https://c.animaapp.com/m9lp78jyHFl4Jh/img/frame-16.svg" />
                          <a href="orders.php" class="view-link">For More Info →</a>
                      </div>
                      
                  </div>
  
                  <div class="stat-card">
                      <div class="stat-content">
                          <div class="stat-info">
                              <p class="stat-label">Orders Received Today</p>
                              <p class="stat-value"><?php echo $stats['today_orders'] ?? 0; ?></p>
                          </div>
                          <div class="stat-icon orders"></div>
                      </div>
                      <div class="stat-footer">
                          <span class="stat-detail completed">
                              <img src="https://c.animaapp.com/m9lp78jyHFl4Jh/img/frame-18.svg" />
                              <?php echo $stats['completed_orders'] ?? 0; ?> Completed
                          </span>
                          <span class="stat-detail pending">
                              <img src="front_end/dashboard/pending.png" />
                              <?php echo $stats['pending_orders'] ?? 0; ?> Pending
                          </span>
                      </div>
                  </div>
  
                  <div class="stat-card">
                      <div class="stat-content">
                          <div class="stat-info">
                              <p class="stat-label">Out of Stock Meals</p>
                              <p class="stat-value"><?php echo $stats['out_of_stock'] ?? 0; ?></p>
                          </div>
                          <div class="stat-icon stock"></div>
                      </div>
                      <div class="stat-footer">
                          <img src="https://c.animaapp.com/m9lp78jyHFl4Jh/img/frame-16.svg" />
                          <a href="inventory.php" class="view-link">View out of stock items →</a>
                      </div>
                  </div>
              </div>
  
              <section class="orders-section">
                  <div class="orders-header">
                      <h2 class="orders-title">Today's Orders</h2>
                      <a href="orders.php" class="view-all">
                          View all orders
                          <img src="https://c.animaapp.com/m9lp78jyHFl4Jh/img/frame-19.svg" />
                      </a>
                  </div>
  
                  <table class="orders-table">
                      <thead>
                          <tr>
                              <th>Order ID</th>
                              <th>Items</th>
                              <th>Type</th>
                              <th>Total</th>
                              <th>Time</th>
                              <th>Status</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($orders as $order): 
                              $order_time = date("h:i A", strtotime($order['order_date']));
                              $order_type_class = strtolower(str_replace(' ', '-', $order['ord_type']));
                              $order_status_class = strtolower(str_replace(' ', '-', $order['order_status']));
                          ?>
                          <tr>
                              <td>#ORD<?php echo str_pad($order['order_id'], 3, '0', STR_PAD_LEFT); ?></td>
                              <td>
                                  <?php 
                                  $items_display = [];
                                  foreach ($order['items'] as $item) {
                                      if ($order['ord_type'] == 'customized') {
                                          // Show abbreviated custom order description
                                          $short_desc = strlen($item['description']) > 50 
                                              ? substr($item['description'], 0, 50) . '...' 
                                              : $item['description'];
                                          $items_display[] = '<span title="'.htmlspecialchars($item['description']).'">Custom: '.htmlspecialchars($short_desc).'</span>';
                                      } else {
                                          $items_display[] = $item['quantity'] . 'x ' . $item['name'];
                                      }
                                  }
                                  echo implode(', ', $items_display);
                                  ?>
                              </td>
                              <td><span class="order-type <?php echo $order_type_class; ?>"><?php echo ucfirst($order['ord_type']); ?></span></td>
                              <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                              <td><?php echo $order_time; ?></td>
                              <td><span class="order-status <?php echo $order_status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?></span></td>
                          </tr>
                          <?php endforeach; ?>
                      </tbody>
                  </table>
              </section>
          </main>
      </div>
  </body>
</html>