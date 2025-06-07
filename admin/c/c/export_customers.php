<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: manage_categories.php');
    exit();
}

require_once 'db_connect.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="customers_export_' . date('Y-m-d_H-i-s') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Customer ID',
    'Name',
    'Email',
    'Phone',
    'Gender',
    'Age',
    'Address',
    'Status',
    'Subscription',
    'Registration Date',
    'Last Login',
    'Total Orders',
    'Total Spent',
    'Last Order Date',
    'Custom Orders',
    'Cart Items'
]);

// Fetch customer data
$query = "
    SELECT c.user_id, u.u_name, u.mail, u.phone, c.gender, 
           YEAR(CURDATE()) - YEAR(c.BOD) as age, eu.address,
           c.status, c.is_subscribed, c.registration_date, c.last_login,
           COUNT(DISTINCT o.order_id) as total_orders,
           COALESCE(SUM(o.total_price), 0) as total_spent,
           MAX(o.order_date) as last_order_date,
           COUNT(DISTINCT co.order_id) as custom_orders,
           COUNT(DISTINCT cart.cart_id) as cart_items
    FROM customer c
    JOIN users u ON c.user_id = u.user_id
    JOIN external_user eu ON c.user_id = eu.user_id
    LEFT JOIN orders o ON c.user_id = o.customer_id
    LEFT JOIN customized_order co ON c.user_id = co.customer_id
    LEFT JOIN cart ON c.user_id = cart.customer_id
    GROUP BY c.user_id
    ORDER BY c.registration_date DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['user_id'],
        $row['u_name'],
        $row['mail'],
        $row['phone'],
        $row['gender'],
        $row['age'],
        $row['address'],
        ucfirst($row['status']),
        $row['is_subscribed'] ? 'Yes' : 'No',
        date('Y-m-d H:i:s', strtotime($row['registration_date'])),
        $row['last_login'] ? date('Y-m-d H:i:s', strtotime($row['last_login'])) : 'Never',
        $row['total_orders'],
        number_format($row['total_spent'], 2),
        $row['last_order_date'] ? date('Y-m-d H:i:s', strtotime($row['last_order_date'])) : 'No orders',
        $row['custom_orders'],
        $row['cart_items']
    ]);
}

fclose($output);
exit();
?> 