<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

require_once 'connection.php';

$kitchen_id = (int)($_GET['id'] ?? 0);

if ($kitchen_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid kitchen ID']);
    exit();
}

try {
    // Get current status
    $currentQuery = "SELECT status, business_name FROM cloud_kitchen_owner cko 
                     JOIN users u ON cko.user_id = u.user_id 
                     WHERE cko.user_id = ?";
    $stmt = $conn->prepare($currentQuery);
    $stmt->bind_param("i", $kitchen_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Kitchen not found']);
        exit();
    }
    
    $kitchen = $result->fetch_assoc();
    $currentStatus = $kitchen['status'];
    $businessName = $kitchen['business_name'];
    
    // Determine new status (cycle through: active -> suspended -> blocked -> active)
    $newStatus = match($currentStatus) {
        'active' => 'suspended',
        'suspended' => 'blocked',
        'blocked' => 'active',
        default => 'active'
    };
    
    // Update status
    $updateQuery = "UPDATE cloud_kitchen_owner SET status = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $newStatus, $kitchen_id);
    
    if ($updateStmt->execute()) {
        // Log the action
        $admin_id = $_SESSION['admin_id'] ?? 1;
        $action_type = "Status Changed: {$currentStatus} → {$newStatus}";
        $action_target = "Kitchen: {$businessName} (ID: {$kitchen_id})";
        
        $logQuery = "INSERT INTO admin_actions (admin_id, action_type, action_target) VALUES (?, ?, ?)";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("iss", $admin_id, $action_type, $action_target);
        $logStmt->execute();
        
        echo json_encode([
            'success' => true, 
            'message' => "Kitchen status updated to {$newStatus}",
            'newStatus' => $newStatus
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>