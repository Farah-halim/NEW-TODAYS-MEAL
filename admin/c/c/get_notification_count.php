<?php
header('Content-Type: application/json');
require_once 'connection.php';

// Get count of pending registrations
$query = "SELECT COUNT(*) as count FROM cloud_kitchen_owner WHERE is_approved = 0";
$result = $conn->query($query);
$data = $result->fetch_assoc();

echo json_encode(['count' => (int)$data['count']]);
?> 