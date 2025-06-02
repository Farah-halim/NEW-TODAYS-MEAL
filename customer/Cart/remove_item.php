<?php
session_start();
require_once('../DB_connection.php');

if (!isset($_SESSION['user_id']) || !isset($_POST['cart_item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$cartItemId = (int)$_POST['cart_item_id'];

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // First, get the cart_id for this item
    $cartQuery = "SELECT cart_id FROM cart_items WHERE cart_item_id = $cartItemId";
    $cartResult = mysqli_query($conn, $cartQuery);
    
    if (!$cartResult || mysqli_num_rows($cartResult) == 0) {
        throw new Exception("Item not found");
    }
    
    $cartData = mysqli_fetch_assoc($cartResult);
    $cart_id = $cartData['cart_id'];
    
    // Delete the item
    $deleteQuery = "DELETE FROM cart_items WHERE cart_item_id = $cartItemId";
    $deleteResult = mysqli_query($conn, $deleteQuery);
    
    if (!$deleteResult) {
        throw new Exception("Failed to delete item");
    }
    
    // Check if cart is now empty
    $checkQuery = "SELECT COUNT(*) FROM cart_items WHERE cart_id = $cart_id";
    $checkResult = mysqli_query($conn, $checkQuery);
    $count = mysqli_fetch_row($checkResult)[0];
    
    if ($count == 0) {
        // Delete the empty cart
        $deleteCartQuery = "DELETE FROM cart WHERE cart_id = $cart_id";
        mysqli_query($conn, $deleteCartQuery);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>