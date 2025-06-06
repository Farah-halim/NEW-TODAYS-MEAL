<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once 'config.php';

// Fetch orders with related information
$query = "SELECT o.*, c.u_name as customer_name, cko.business_name as kitchen_name,
          dd.d_status as delivery_status, u.u_name as delivery_man_name
          FROM orders o
          LEFT JOIN customer cu ON o.customer_id = cu.user_id
          LEFT JOIN users c ON cu.user_id = c.user_id
          LEFT JOIN cloud_kitchen_owner cko ON o.cloud_kitchen_id = cko.user_id
          LEFT JOIN delivery_details dd ON o.order_id = dd.ord_id
          LEFT JOIN users u ON dd.user_id = u.user_id
          ORDER BY o.order_date DESC";
$result = $conn->query($query);

// Prepare the response
$orders = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($orders); 