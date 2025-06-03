<?php
/**
 * API endpoint to get deliveries
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

// Get status filter if provided
$status = isset($_GET['status']) ? trim($_GET['status']) : null;

// Get deliveries for the current user
$deliveries = get_deliveries($status);

// Return response
header('Content-Type: application/json');
echo json_encode($deliveries);
?>