<?php
/**
 * API endpoint to get delivery details
 */
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

// Get delivery ID from request
$deliveryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate ID
if (empty($deliveryId)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing delivery ID']);
    exit;
}

// Get delivery details
$delivery = get_delivery_with_details($deliveryId);

// Check if delivery exists and belongs to the current user
if (!$delivery || $delivery['delivery_person_id'] != $_SESSION['user_id']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Delivery not found or access denied']);
    exit;
}

// Return delivery details
header('Content-Type: application/json');
echo json_encode(['success' => true, 'delivery' => $delivery]);
?>