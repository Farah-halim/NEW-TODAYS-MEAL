<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get form data
$order_id = isset($_POST['order_id']) && is_numeric($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$order_status = isset($_POST['order_status']) ? $_POST['order_status'] : '';
$kitchen_status = isset($_POST['kitchen_status']) ? $_POST['kitchen_status'] : '';
$notes = isset($_POST['notes']) ? $_POST['notes'] : '';

// Validate required fields
if ($order_id <= 0 || empty($order_status)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Validate status values
$valid_order_statuses = ['pending', 'in_progress', 'delivered', 'cancelled'];
$valid_kitchen_statuses = ['new', 'preparing', 'ready_for_delivery', 'delivered'];

if (!in_array($order_status, $valid_order_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid order status']);
    exit();
}

if (!empty($kitchen_status) && !in_array($kitchen_status, $valid_kitchen_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid kitchen status']);
    exit();
}

// Update order status
$query = "UPDATE orders SET order_status = ?";
$params = [$order_status];
$types = "s";

// Add kitchen status if provided
if (!empty($kitchen_status)) {
    $query .= ", kitchen_order_status = ?";
    $params[] = $kitchen_status;
    $types .= "s";
}

$query .= " WHERE order_id = ?";
$params[] = $order_id;
$types .= "i";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$result = $stmt->execute();

if ($result) {
    // Log the status change (in a real system, you'd have a status_history table)
    // For now, we'll just consider it successful
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to update order status: ' . $conn->error]);
} 