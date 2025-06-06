<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Set column headers
fputcsv($output, [
    'Order ID',
    'Date',
    'Customer',
    'Cloud Kitchen',
    'Type',
    'Total Price',
    'Status',
    'Kitchen Status',
    'Delivery Zone',
    'Delivery Status',
    'Delivery Person'
]);

// Get filtered parameters if provided
$where_clause = "1=1";
$params = [];
$types = "";

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_clause .= " AND o.order_status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $where_clause .= " AND o.order_date >= ?";
    $params[] = $_GET['start_date'] . " 00:00:00";
    $types .= "s";
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $where_clause .= " AND o.order_date <= ?";
    $params[] = $_GET['end_date'] . " 23:59:59";
    $types .= "s";
}

if (isset($_GET['order_type']) && !empty($_GET['order_type'])) {
    $where_clause .= " AND o.ord_type = ?";
    $params[] = $_GET['order_type'];
    $types .= "s";
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
          WHERE $where_clause
          ORDER BY o.order_date DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Export data
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['order_id'],
        date('Y-m-d H:i:s', strtotime($row['order_date'])),
        $row['customer_name'] ?? 'Unknown',
        $row['kitchen_name'] ?? 'Unknown',
        ucfirst($row['ord_type']),
        number_format($row['total_price'], 2),
        ucfirst($row['order_status']),
        ucfirst($row['kitchen_order_status']),
        $row['delivery_zone'],
        $row['delivery_status'] ? ucfirst($row['delivery_status']) : 'Not Assigned',
        $row['delivery_man_name'] ?? 'Not Assigned'
    ]);
}

// Close output stream
fclose($output); 