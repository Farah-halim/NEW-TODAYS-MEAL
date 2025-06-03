<?php
session_start();
require_once('../DB_connection.php');

header('Content-Type: application/json');

try {
    // Validate session and input
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not logged in', 401);
    }

    if (!isset($_POST['cart_item_id']) || !isset($_POST['change'])) {
        throw new Exception('Invalid request', 400);
    }

    $cartItemId = (int)$_POST['cart_item_id'];
    $change = (int)$_POST['change'];
    $ignoreStock = isset($_POST['ignore_stock']) && $_POST['ignore_stock'] === 'true';

    // Get current cart item details
    $query = "SELECT ci.quantity, ci.meal_id, m.stock_quantity 
              FROM cart_items ci
              JOIN meals m ON ci.meal_id = m.meal_id
              WHERE ci.cart_item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $cartItemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Item not found in cart', 404);
    }

    $item = $result->fetch_assoc();
    $newQuantity = $item['quantity'] + $change;

    // Validate quantity
    if ($newQuantity < 1) {
        throw new Exception('Quantity cannot be less than 1', 400);
    }


    // Update quantity using prepared statement
    $updateQuery = "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $newQuantity, $cartItemId);
    $success = $updateStmt->execute();

    if (!$success) {
        throw new Exception('Failed to update quantity: ' . $conn->error, 500);
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'newQuantity' => $newQuantity
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>