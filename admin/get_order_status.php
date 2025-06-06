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

// Fetch order status information
$query = "SELECT order_status, kitchen_order_status 
          FROM orders 
          WHERE order_id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Order not found']);
    exit();
}

$statusData = $result->fetch_assoc();

// Return the status data as JSON
header('Content-Type: application/json');
echo json_encode($statusData); 