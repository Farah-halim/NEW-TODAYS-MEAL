<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once 'config.php';

// Get order ID from request
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($orderId <= 0) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid order ID']);
    exit();
}

// Fetch order details
$query = "SELECT o.*, c.u_name as customer_name, cko.business_name as kitchen_name,
          dd.d_status as delivery_status, u.u_name as delivery_man_name,
          dd.d_location, dd.p_method, dd.delivery_date_and_time
          FROM orders o
          LEFT JOIN customer cu ON o.customer_id = cu.user_id
          LEFT JOIN users c ON cu.user_id = c.user_id
          LEFT JOIN cloud_kitchen_owner cko ON o.cloud_kitchen_id = cko.user_id
          LEFT JOIN delivery_details dd ON o.order_id = dd.ord_id
          LEFT JOIN users u ON dd.user_id = u.user_id
          WHERE o.order_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Order not found']);
    exit();
}

$order = $result->fetch_assoc();

// Fetch order items
$itemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
$itemsStmt = $conn->prepare($itemsQuery);
$itemsStmt->bind_param('i', $orderId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();

$items = [];
while ($item = $itemsResult->fetch_assoc()) {
    $items[] = $item;
}

// Add items to order data
$order['items'] = $items;

// Fetch delivery details
$deliveryQuery = "SELECT * FROM delivery_details WHERE ord_id = ?";
$deliveryStmt = $conn->prepare($deliveryQuery);
$deliveryStmt->bind_param('i', $orderId);
$deliveryStmt->execute();
$deliveryResult = $deliveryStmt->get_result();

if ($deliveryResult->num_rows > 0) {
    $order['delivery_details'] = $deliveryResult->fetch_assoc();
}

// Fetch customized order details if applicable
if ($order['ord_type'] === 'customized') {
    $customizedQuery = "SELECT * FROM customized_orders WHERE order_id = ?";
    $customizedStmt = $conn->prepare($customizedQuery);
    $customizedStmt->bind_param('i', $orderId);
    $customizedStmt->execute();
    $customizedResult = $customizedStmt->get_result();
    
    if ($customizedResult->num_rows > 0) {
        $order['customized_details'] = $customizedResult->fetch_assoc();
    }
}

// Return order details as JSON
header('Content-Type: application/json');
echo json_encode($order); 