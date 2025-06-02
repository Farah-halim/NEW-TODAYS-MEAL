<?php
include "../DB_connection.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get input data
$order_id = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$order_id || !$status) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

if ($conn) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        if ($status === 'price_accepted') {
            // Update customer approval status
            $sql = "UPDATE customized_order SET customer_approval = 'approved' WHERE order_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
        } else if ($status === 'rejected') {
            // Update customer approval status to rejected
            $sql = "UPDATE customized_order SET customer_approval = 'rejected' WHERE order_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            
            // Then delete the order from the orders table (will cascade to customized_order)
            $delete_sql = "DELETE FROM orders WHERE order_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $order_id);
            $delete_stmt->execute();
            
            // Check if any rows were affected
            if ($delete_stmt->affected_rows === 0) {
                throw new Exception("No order found with that ID");
            }
        } else if ($status === 'delete_rejected') {
            // First verify the order is actually rejected
            $verify_sql = "SELECT co.order_id 
                          FROM customized_order co
                          JOIN orders o ON co.order_id = o.order_id
                          WHERE co.order_id = ? 
                          AND (co.customer_approval = 'rejected' OR co.status = 'rejected')";
            $verify_stmt = $conn->prepare($verify_sql);
            $verify_stmt->bind_param("i", $order_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result->num_rows === 0) {
                throw new Exception("Order is not in rejected status or doesn't exist");
            }
            
            // Then delete
            $delete_sql = "DELETE FROM orders WHERE order_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $order_id);
            $delete_stmt->execute();
            
            if ($delete_stmt->affected_rows === 0) {
                throw new Exception("Failed to delete order");
            }
        } else {
            throw new Exception("Invalid status");
        }
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
}
?>