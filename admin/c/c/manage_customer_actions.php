<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = $_POST['action'] ?? '';
$customerId = $_POST['customer_id'] ?? '';

if (empty($action) || empty($customerId)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

try {
    switch ($action) {
        case 'block':
            $result = blockCustomer($pdo, $customerId);
            break;
        case 'activate':
            $result = activateCustomer($pdo, $customerId);
            break;
        case 'delete':
            $result = deleteCustomer($pdo, $customerId);
            break;
        default:
            $result = ['success' => false, 'message' => 'Invalid action'];
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

function blockCustomer($pdo, $customerId) {
    try {
        // Check if customer exists and is active
        $checkQuery = "SELECT u.u_name FROM customer c 
                       JOIN users u ON c.user_id = u.user_id 
                       WHERE c.user_id = ? AND c.status = 'active'";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$customerId]);
        $customer = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            return ['success' => false, 'message' => 'Customer not found or already blocked'];
        }
        
        // Update customer status to blocked
        $updateQuery = "UPDATE customer SET status = 'blocked' WHERE user_id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$customerId]);
        
        // Log admin action
        logAdminAction($pdo, 'Blocked customer', $customer['u_name']);
        
        return ['success' => true, 'message' => 'Customer blocked successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to block customer: ' . $e->getMessage()];
    }
}

function activateCustomer($pdo, $customerId) {
    try {
        // Check if customer exists and is blocked
        $checkQuery = "SELECT u.u_name FROM customer c 
                       JOIN users u ON c.user_id = u.user_id 
                       WHERE c.user_id = ? AND c.status = 'blocked'";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$customerId]);
        $customer = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            return ['success' => false, 'message' => 'Customer not found or already active'];
        }
        
        // Update customer status to active
        $updateQuery = "UPDATE customer SET status = 'active' WHERE user_id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$customerId]);
        
        // Log admin action
        logAdminAction($pdo, 'Activated customer', $customer['u_name']);
        
        return ['success' => true, 'message' => 'Customer activated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to activate customer: ' . $e->getMessage()];
    }
}

function deleteCustomer($pdo, $customerId) {
    try {
        $pdo->beginTransaction();
        
        // Get customer name for logging
        $nameQuery = "SELECT u.u_name FROM customer c 
                      JOIN users u ON c.user_id = u.user_id 
                      WHERE c.user_id = ?";
        $nameStmt = $pdo->prepare($nameQuery);
        $nameStmt->execute([$customerId]);
        $customer = $nameStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Customer not found'];
        }
        
        // Delete related records first (to maintain referential integrity)
        
        // Delete cart items
        $deleteCartItemsQuery = "DELETE ci FROM cart_items ci 
                                 JOIN cart c ON ci.cart_id = c.cart_id 
                                 WHERE c.customer_id = ?";
        $pdo->prepare($deleteCartItemsQuery)->execute([$customerId]);
        
        // Delete cart
        $deleteCartQuery = "DELETE FROM cart WHERE customer_id = ?";
        $pdo->prepare($deleteCartQuery)->execute([$customerId]);
        
        // Delete reviews
        $deleteReviewsQuery = "DELETE FROM reviews WHERE customer_id = ?";
        $pdo->prepare($deleteReviewsQuery)->execute([$customerId]);
        
        // Delete order content first (foreign key constraint)
        $deleteOrderContentQuery = "DELETE oc FROM order_content oc 
                                     JOIN orders o ON oc.order_id = o.order_id 
                                     WHERE o.customer_id = ?";
        $pdo->prepare($deleteOrderContentQuery)->execute([$customerId]);
        
        // Delete payment details
        $deletePaymentQuery = "DELETE pd FROM payment_details pd 
                               JOIN orders o ON pd.order_id = o.order_id 
                               WHERE o.customer_id = ?";
        $pdo->prepare($deletePaymentQuery)->execute([$customerId]);
        
        // Delete customized orders
        $deleteCustomOrdersQuery = "DELETE FROM customized_order WHERE customer_id = ?";
        $pdo->prepare($deleteCustomOrdersQuery)->execute([$customerId]);
        
        // Delete orders
        $deleteOrdersQuery = "DELETE FROM orders WHERE customer_id = ?";
        $pdo->prepare($deleteOrdersQuery)->execute([$customerId]);
        
        // Delete delivery subscriptions
        $deleteSubscriptionsQuery = "DELETE FROM delivery_subscriptions WHERE customer_id = ?";
        $pdo->prepare($deleteSubscriptionsQuery)->execute([$customerId]);
        
        // Delete complaints
        $deleteComplaintsQuery = "DELETE FROM complaints WHERE customer_id = ?";
        $pdo->prepare($deleteComplaintsQuery)->execute([$customerId]);
        
        // Delete deliveries
        $deleteDeliveriesQuery = "DELETE FROM deliveries WHERE customer_id = ?";
        $pdo->prepare($deleteDeliveriesQuery)->execute([$customerId]);
        
        // Delete external user record
        $deleteExternalUserQuery = "DELETE FROM external_user WHERE user_id = ?";
        $pdo->prepare($deleteExternalUserQuery)->execute([$customerId]);
        
        // Delete customer record
        $deleteCustomerQuery = "DELETE FROM customer WHERE user_id = ?";
        $pdo->prepare($deleteCustomerQuery)->execute([$customerId]);
        
        // Delete user record (this should cascade)
        $deleteUserQuery = "DELETE FROM users WHERE user_id = ?";
        $pdo->prepare($deleteUserQuery)->execute([$customerId]);
        
        $pdo->commit();
        
        // Log admin action
        logAdminAction($pdo, 'Deleted customer', $customer['u_name']);
        
        return ['success' => true, 'message' => 'Customer deleted successfully'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to delete customer: ' . $e->getMessage()];
    }
}

function logAdminAction($pdo, $action, $target) {
    try {
        $adminId = $_SESSION['admin_user_id'] ?? 1; // Default to 1 if not set
        $insertQuery = "INSERT INTO admin_actions (admin_id, action_type, action_target) VALUES (?, ?, ?)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([$adminId, $action, $target]);
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Failed to log admin action: " . $e->getMessage());
    }
}
?> 