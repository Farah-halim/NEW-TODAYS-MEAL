<?php
session_start();
require_once '../DB_connection.php';
header('Content-Type: application/json'); 
if (isset($_POST['cart_id']) && is_numeric($_POST['cart_id'])) {
    if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
        $cart_id = (int)$_POST['cart_id'];
        $user_id = (int)$_SESSION['user_id'];

        $stmt = $conn->prepare("SELECT 1 FROM cart WHERE cart_id = ? AND customer_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $cart_id, $user_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $_SESSION['cart_id'] = $cart_id;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid cart ID for this user']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User is not logged in']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No valid cart ID provided']);
}
?>