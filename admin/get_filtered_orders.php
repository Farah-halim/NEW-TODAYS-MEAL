<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once 'config.php';

// Get filter parameters
$startDate = isset($_POST['start_date']) && !empty($_POST['start_date']) ? $conn->real_escape_string($_POST['start_date']) : null;
$endDate = isset($_POST['end_date']) && !empty($_POST['end_date']) ? $conn->real_escape_string($_POST['end_date']) : null;
$status = isset($_POST['status']) && !empty($_POST['status']) ? $conn->real_escape_string($_POST['status']) : null;
$orderType = isset($_POST['order_type']) && !empty($_POST['order_type']) ? $conn->real_escape_string($_POST['order_type']) : null;
$minPrice = isset($_POST['min_price']) && !empty($_POST['min_price']) ? floatval($_POST['min_price']) : null;
$maxPrice = isset($_POST['max_price']) && !empty($_POST['max_price']) ? floatval($_POST['max_price']) : null;

// Build the query
$query = "SELECT o.*, c.u_name as customer_name, cko.business_name as kitchen_name,
          dd.d_status as delivery_status, u.u_name as delivery_man_name
          FROM orders o
          LEFT JOIN customer cu ON o.customer_id = cu.user_id
          LEFT JOIN users c ON cu.user_id = c.user_id
          LEFT JOIN cloud_kitchen_owner cko ON o.cloud_kitchen_id = cko.user_id
          LEFT JOIN delivery_details dd ON o.order_id = dd.ord_id
          LEFT JOIN users u ON dd.user_id = u.user_id
          WHERE 1=1";

// Add filter conditions
if ($startDate) {
    $query .= " AND o.order_date >= '$startDate'";
}
if ($endDate) {
    $query .= " AND o.order_date <= '$endDate 23:59:59'";
}
if ($status) {
    $query .= " AND o.order_status = '$status'";
}
if ($orderType) {
    $query .= " AND o.ord_type = '$orderType'";
}
if ($minPrice !== null) {
    $query .= " AND o.total_price >= $minPrice";
}
if ($maxPrice !== null) {
    $query .= " AND o.total_price <= $maxPrice";
}

// Order by date
$query .= " ORDER BY o.order_date DESC";

// Execute the query
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