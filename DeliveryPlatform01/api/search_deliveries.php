<?php
/**
 * API endpoint to search deliveries
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

// Get search query
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Validate query
if (empty($query)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

// Search deliveries
$results = search_deliveries($query);

// Return results
header('Content-Type: application/json');
echo json_encode($results);
?>