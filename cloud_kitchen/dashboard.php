<?php
session_start();
require_once '../DB_connection.php'; // Assuming you have a database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {

    header("Location: \NEW-TODAYS-MEAL\1 - Register & Login Codes\login.php");
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
    header("Location: unauthorized.php"); // Redirect if not authorized
    exit();
}

// Store cloud kitchen ID in session for future use
$_SESSION['cloud_kitchen_id'] = $user['cloud_kitchen_id'];
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
                              <p class="stat-value"><span>$</span>1875.50</p>
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
                              <p class="stat-value">48</p>
                          </div>
                          <div class="stat-icon orders"></div>
                      </div>
                      <div class="stat-footer">
                          <span class="stat-detail completed">
                              <img src="https://c.animaapp.com/m9lp78jyHFl4Jh/img/frame-18.svg" />
                              32 Completed
                          </span>
                          <span class="stat-detail pending">
                              <img src="front_end/dashboard/pending.png" />
                              16 Pending
                          </span>
                      </div>
                  </div>
  
                  <div class="stat-card">
                      <div class="stat-content">
                          <div class="stat-info">
                              <p class="stat-label">Out of Stock Meals</p>
                              <p class="stat-value">3</p>
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
                          <tr>
                              <td>#ORD001</td>
                              <td>2x Grilled Chicken, 1x Caesar Salad</td>
                              <td><span class="order-type normal">Normal</span></td>
                              <td>$45.98</td>
                              <td>10:30 AM</td>
                              <td><span class="order-status completed">Completed</span></td>
                          </tr>
                          <tr>
                              <td>#ORD002</td>
                              <td>1x Vegetable Salad, 2x Fruit Smoothie</td>
                              <td><span class="order-type scheduled">Weekly Scheduled</span></td>
                              <td>$28.97</td>
                              <td>11:15 AM</td>
                              <td><span class="order-status preparing">Preparing</span></td>
                          </tr>
                          <tr>
                              <td>#ORD003</td>
                              <td>3x Pasta Carbonara</td>
                              <td><span class="order-type normal">Normal</span></td>
                              <td>$44.97</td>
                              <td>11:45 AM</td>
                              <td><span class="order-status pending">Pending</span></td>
                          </tr>
                          <tr>
                              <td>#ORD004</td>
                              <td>1x Grilled Chicken, 1x Chocolate Cake</td>
                              <td><span class="order-type scheduled">Weekly Scheduled</span></td>
                              <td>$32.98</td>
                              <td>12:00 PM</td>
                              <td><span class="order-status ready">Ready</span></td>
                          </tr>
                          <tr>
                              <td>#ORD005</td>
                              <td>2x Caesar Salad, 1x Fruit Smoothie</td>
                              <td><span class="order-type normal">Normal</span></td>
                              <td>$35.97</td>
                              <td>12:30 PM</td>
                              <td><span class="order-status pending">Pending</span></td>
                          </tr>
                      </tbody>
                  </table>
              </section>
          </main>
      </div>
  </body>