<?php
session_start();
require_once('../DB_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

if (!isset($_GET['meal_id']) || !isset($_GET['kitchen_id'])) {
    header("Location: /NEW-TODAYS-MEAL/customer/Show_Caterers/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$meal_id = (int)$_GET['meal_id'];
$kitchen_id = (int)$_GET['kitchen_id'];

// Check if meal exists and get its price
$meal_query = "SELECT price FROM meals WHERE meal_id = $meal_id AND cloud_kitchen_id = $kitchen_id";
$meal_result = mysqli_query($conn, $meal_query);

if (mysqli_num_rows($meal_result) == 0) {
    header("Location: /NEW-TODAYS-MEAL/customer/Menu/index.php?kitchen_id=$kitchen_id&error=invalid_meal");
    exit();
}

$meal = mysqli_fetch_assoc($meal_result);
$price = $meal['price'];

// Check if user already has a cart for this kitchen
$cart_query = "SELECT cart_id FROM cart WHERE customer_id = $user_id AND cloud_kitchen_id = $kitchen_id LIMIT 1";
$cart_result = mysqli_query($conn, $cart_query);

if (mysqli_num_rows($cart_result) > 0) {
    // Existing cart found
    $cart = mysqli_fetch_assoc($cart_result);
    $cart_id = $cart['cart_id'];
    
    // Check if meal already in cart
    $item_query = "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = $cart_id AND meal_id = $meal_id LIMIT 1";
    $item_result = mysqli_query($conn, $item_query);
    
    if (mysqli_num_rows($item_result) > 0) {
        // Update quantity
        $item = mysqli_fetch_assoc($item_result);
        $new_quantity = $item['quantity'] + 1;
        $update_query = "UPDATE cart_items SET quantity = $new_quantity WHERE cart_item_id = {$item['cart_item_id']}";
        mysqli_query($conn, $update_query);
    } else {
        // Add new item
        $insert_query = "INSERT INTO cart_items (cart_id, meal_id, quantity, price) 
                         VALUES ($cart_id, $meal_id, 1, $price)";
        mysqli_query($conn, $insert_query);
    }
} else {
    // Create new cart and add item
    $insert_cart = "INSERT INTO cart (customer_id, cloud_kitchen_id) VALUES ($user_id, $kitchen_id)";
    mysqli_query($conn, $insert_cart);
    $cart_id = mysqli_insert_id($conn);
    
    $insert_item = "INSERT INTO cart_items (cart_id, meal_id, quantity, price) 
                    VALUES ($cart_id, $meal_id, 1, $price)";
    mysqli_query($conn, $insert_item);
}

// At the end of add_to_cart.php (after successful add)
echo json_encode(['success' => true]);
exit();

// For errors
echo json_encode(['success' => false, 'message' => 'Item out of stock']);
exit();
?>