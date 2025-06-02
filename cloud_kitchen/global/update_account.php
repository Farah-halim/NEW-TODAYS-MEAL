<?php
session_start();
require_once __DIR__ .  '/../../DB_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$userId = $_SESSION['user_id'];
$name = $_POST['name'] ?? '';
$address = $_POST['address'] ?? '';
$businessName = $_POST['business_name'] ?? '';

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Update users table
    $stmt = $conn->prepare("UPDATE users SET u_name = ? WHERE user_id = ?");
    $stmt->bind_param("si", $name, $userId);
    $stmt->execute();
    
    // Update external_user table
    $stmt = $conn->prepare("UPDATE external_user SET address = ? WHERE user_id = ?");
    $stmt->bind_param("si", $address, $userId);
    $stmt->execute();
    
    // Update cloud_kitchen_owner table
    $stmt = $conn->prepare("UPDATE cloud_kitchen_owner SET business_name = ? WHERE user_id = ?");
    $stmt->bind_param("si", $businessName, $userId);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'updatedData' => [
            'name' => $name,
            'address' => $address,
            'business_name' => $businessName
        ]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>