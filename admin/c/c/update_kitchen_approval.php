<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = $_POST['action'] ?? '';
$kitchen_id = (int)($_POST['kitchen_id'] ?? 0);

if (!in_array($action, ['approve', 'reject']) || $kitchen_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Get admin ID
$admin_id = $_SESSION['admin_id'] ?? 1; // Default to admin user_id = 1

try {
    $conn->autocommit(FALSE);

    if ($action === 'approve') {
        // Update kitchen approval status
        $updateQuery = "UPDATE cloud_kitchen_owner SET is_approved = 1, status = 'active' WHERE user_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $kitchen_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update kitchen status');
        }

        // Get kitchen business name for logging
        $nameQuery = "SELECT business_name FROM cloud_kitchen_owner WHERE user_id = ?";
        $nameStmt = $conn->prepare($nameQuery);
        $nameStmt->bind_param("i", $kitchen_id);
        $nameStmt->execute();
        $result = $nameStmt->get_result();
        $kitchen = $result->fetch_assoc();
        $business_name = $kitchen['business_name'] ?? 'Unknown Kitchen';

        // Log admin action
        $logQuery = "INSERT INTO admin_actions (admin_id, action_type, action_target, created_at) VALUES (?, ?, ?, NOW())";
        $logStmt = $conn->prepare($logQuery);
        $action_type = 'Approved Kitchen';
        $action_target = $business_name;
        $logStmt->bind_param("iss", $admin_id, $action_type, $action_target);
        $logStmt->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Kitchen approved successfully']);

    } else if ($action === 'reject') {
        // Get kitchen business name for logging before deletion
        $nameQuery = "SELECT business_name FROM cloud_kitchen_owner WHERE user_id = ?";
        $nameStmt = $conn->prepare($nameQuery);
        $nameStmt->bind_param("i", $kitchen_id);
        $nameStmt->execute();
        $result = $nameStmt->get_result();
        $kitchen = $result->fetch_assoc();
        $business_name = $kitchen['business_name'] ?? 'Unknown Kitchen';

        // Delete kitchen record (this will cascade to related tables)
        $deleteQuery = "DELETE FROM cloud_kitchen_owner WHERE user_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $kitchen_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to reject kitchen registration');
        }

        // Log admin action
        $logQuery = "INSERT INTO admin_actions (admin_id, action_type, action_target, created_at) VALUES (?, ?, ?, NOW())";
        $logStmt = $conn->prepare($logQuery);
        $action_type = 'Rejected Kitchen';
        $action_target = $business_name;
        $logStmt->bind_param("iss", $admin_id, $action_type, $action_target);
        $logStmt->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Kitchen registration rejected']);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->autocommit(TRUE);
?> 