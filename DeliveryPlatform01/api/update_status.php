<?php
/**
 * API endpoint to update delivery status
 */
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Require user to be logged in
if (!is_logged_in()) {
    header('HTTP/1.1 401 Unauthorized');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get data from POST
$deliveryId = isset($_POST['delivery_id']) ? intval($_POST['delivery_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validate input
if (empty($deliveryId) || empty($status)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate status
$validStatuses = ['pending', 'in-progress', 'completed', 'cancelled', 'delayed'];
if (!in_array($status, $validStatuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

// Get delivery details to verify ownership
$delivery = get_delivery($deliveryId);

// Check if delivery exists and belongs to the current user
if (!$delivery || $delivery['delivery_person_id'] != $_SESSION['user_id']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Delivery not found or access denied']);
    exit;
}

// Update delivery status
$result = update_delivery_status($deliveryId, $status);

// Determine response message based on status
$statusMessages = [
    'pending' => 'Delivery has been marked as pending',
    'in-progress' => 'Delivery has been marked as picked up and in progress',
    'completed' => 'Delivery has been marked as delivered and completed',
    'cancelled' => 'Delivery has been cancelled',
    'delayed' => 'Delivery has been marked as delayed'
];

// Return response
header('Content-Type: application/json');
if ($result) {
    echo json_encode([
        'success' => true, 
        'message' => $statusMessages[$status] ?? 'Status updated successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
?>