<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once 'config.php';

// Get POST data
$action = $_POST['action'] ?? '';
$kitchen_id = (int)($_POST['kitchen_id'] ?? 0);
$admin_id = $_SESSION['admin_id'] ?? 1;

// Validate kitchen ID
if ($kitchen_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid kitchen ID']);
    exit();
}

try {
    switch ($action) {
        case 'suspend':
            $reason = trim($_POST['reason'] ?? '');
            if (empty($reason)) {
                echo json_encode(['success' => false, 'message' => 'Suspension reason is required']);
                exit();
            }
            
            // Check if suspension columns exist
            $result = $conn->query("SHOW COLUMNS FROM cloud_kitchen_owner LIKE 'suspension_reason'");
            if ($result->num_rows > 0) {
                // Suspension columns exist, use them
                $stmt = $conn->prepare("UPDATE cloud_kitchen_owner 
                                       SET status = 'suspended', 
                                           suspension_reason = ?, 
                                           suspended_by = ?, 
                                           suspension_date = NOW() 
                                       WHERE user_id = ? AND status = 'active'");
                $stmt->bind_param("sii", $reason, $admin_id, $kitchen_id);
            } else {
                // Suspension columns don't exist yet, just update status
                $stmt = $conn->prepare("UPDATE cloud_kitchen_owner 
                                       SET status = 'suspended' 
                                       WHERE user_id = ? AND status = 'active'");
                $stmt->bind_param("i", $kitchen_id);
            }
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                logAdminAction($conn, $admin_id, 'Suspended kitchen', "Kitchen ID: $kitchen_id, Reason: $reason");
                echo json_encode(['success' => true, 'message' => 'Kitchen suspended successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to suspend kitchen or kitchen not active']);
            }
            break;
            
        case 'unsuspend':
            // Check if suspension columns exist
            $result = $conn->query("SHOW COLUMNS FROM cloud_kitchen_owner LIKE 'suspension_reason'");
            if ($result->num_rows > 0) {
                // Suspension columns exist, clear them
                $stmt = $conn->prepare("UPDATE cloud_kitchen_owner 
                                       SET status = 'active', 
                                           suspension_reason = NULL, 
                                           suspended_by = NULL, 
                                           suspension_date = NULL 
                                       WHERE user_id = ? AND status = 'suspended'");
                $stmt->bind_param("i", $kitchen_id);
            } else {
                // Just update status
                $stmt = $conn->prepare("UPDATE cloud_kitchen_owner 
                                       SET status = 'active' 
                                       WHERE user_id = ? AND status = 'suspended'");
                $stmt->bind_param("i", $kitchen_id);
            }
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                logAdminAction($conn, $admin_id, 'Unsuspended kitchen', "Kitchen ID: $kitchen_id");
                echo json_encode(['success' => true, 'message' => 'Kitchen unsuspended successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to unsuspend kitchen or kitchen not suspended']);
            }
            break;
            
        case 'block':
            // Block kitchen
            $stmt = $conn->prepare("UPDATE cloud_kitchen_owner 
                                   SET status = 'blocked' 
                                   WHERE user_id = ? AND status IN ('active', 'suspended')");
            $stmt->bind_param("i", $kitchen_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                logAdminAction($conn, $admin_id, 'Blocked kitchen', "Kitchen ID: $kitchen_id");
                echo json_encode(['success' => true, 'message' => 'Kitchen blocked successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to block kitchen']);
            }
            break;
            
        case 'unblock':
            // Unblock kitchen (back to active)
            $stmt = $conn->prepare("UPDATE cloud_kitchen_owner 
                                   SET status = 'active' 
                                   WHERE user_id = ? AND status = 'blocked'");
            $stmt->bind_param("i", $kitchen_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                logAdminAction($conn, $admin_id, 'Unblocked kitchen', "Kitchen ID: $kitchen_id");
                echo json_encode(['success' => true, 'message' => 'Kitchen unblocked successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to unblock kitchen or kitchen not blocked']);
            }
            break;
            
        case 'delete':
            // First get kitchen name for logging
            $stmt = $conn->prepare("SELECT business_name FROM cloud_kitchen_owner WHERE user_id = ?");
            $stmt->bind_param("i", $kitchen_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $kitchen = $result->fetch_assoc();
            
            if (!$kitchen) {
                echo json_encode(['success' => false, 'message' => 'Kitchen not found']);
                exit();
            }
            
            // Start transaction for safe deletion
            $conn->begin_transaction();
            
            try {
                // Delete in correct order to avoid foreign key constraint errors
                
                // 1. Delete meal-related records first (these reference meals)
                $stmt = $conn->prepare("DELETE FROM meal_category WHERE meal_id IN (SELECT meal_id FROM meals WHERE cloud_kitchen_id = ?)");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM meal_subcategory WHERE meal_id IN (SELECT meal_id FROM meals WHERE cloud_kitchen_id = ?)");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM order_content WHERE meal_id IN (SELECT meal_id FROM meals WHERE cloud_kitchen_id = ?)");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM meals_in_each_package WHERE meal_id IN (SELECT meal_id FROM meals WHERE cloud_kitchen_id = ?)");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM cart_items WHERE meal_id IN (SELECT meal_id FROM meals WHERE cloud_kitchen_id = ?)");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // 2. Delete meals
                $stmt = $conn->prepare("DELETE FROM meals WHERE cloud_kitchen_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // 3. Delete order-related records
                $stmt = $conn->prepare("DELETE FROM order_packages WHERE order_id IN (SELECT order_id FROM orders WHERE cloud_kitchen_id = ?)");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM payment_details WHERE order_id IN (SELECT order_id FROM orders WHERE cloud_kitchen_id = ?)");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM reviews WHERE cloud_kitchen_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM deliveries WHERE provider_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM orders WHERE cloud_kitchen_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // 4. Delete customized orders
                $stmt = $conn->prepare("DELETE FROM customized_order WHERE kitchen_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // 5. Delete complaints
                $stmt = $conn->prepare("DELETE FROM complaints WHERE kitchen_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // 6. Delete cart records
                $stmt = $conn->prepare("DELETE FROM cart WHERE cloud_kitchen_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // 7. Delete kitchen-specific records
                $stmt = $conn->prepare("DELETE FROM cloud_kitchen_specialist_category WHERE cloud_kitchen_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM caterer_tags WHERE user_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // 8. Delete the kitchen owner record
                $stmt = $conn->prepare("DELETE FROM cloud_kitchen_owner WHERE user_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // 9. Delete from external_user
                $stmt = $conn->prepare("DELETE FROM external_user WHERE user_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // 10. Finally delete the user record
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $kitchen_id);
                $stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                logAdminAction($conn, $admin_id, 'Deleted kitchen', "Kitchen: {$kitchen['business_name']} (ID: $kitchen_id)");
                echo json_encode(['success' => true, 'message' => 'Kitchen deleted successfully']);
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                throw $e;
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Kitchen action error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

function logAdminAction($conn, $admin_id, $action_type, $action_target) {
    try {
        $stmt = $conn->prepare("INSERT INTO admin_actions (admin_id, action_type, action_target, created_at) 
                               VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $admin_id, $action_type, $action_target);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Failed to log admin action: " . $e->getMessage());
    }
}
?> 