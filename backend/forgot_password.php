<?php
include("../DB_connection.php"); 
date_default_timezone_set('Africa/Cairo');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $sql = "SELECT user_id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['user_id'];

        $token = bin2hex(random_bytes(32)); 
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update_sql = "UPDATE users SET reset_token = '$token', reset_token_expiry = '$expiry' WHERE user_id = '$user_id'";
        mysqli_query($conn, $update_sql);

        echo "<script>
        window.location.href = 'http://localhost/Todays-Meal-1/backend/reset_password.php?token=$token';
        </script>";
        exit();

    } else {
        echo "<script>
            alert('❌ Email not found!');
            window.location.href = 'forgot_password.php';
        </script>";
        exit();
    }} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form method="POST" action="forgot_password.php">
        <input type="email" name="email" required placeholder="Enter your email">
        <button type="submit">verify</button>
    </form>
</body>
</html>
