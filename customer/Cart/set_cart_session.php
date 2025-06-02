<?php
session_start();
require_once '../DB_connection.php';

// Check if POST request contains cart_id
if (isset($_POST['cart_id'])) {
    $_SESSION['cart_id'] = $_POST['cart_id'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No cart ID provided']);
}