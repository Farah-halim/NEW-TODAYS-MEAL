<?php
include("../DB_connection.php"); 
date_default_timezone_set('Africa/Cairo');

$message = "";
$email = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $sql = "SELECT email FROM users WHERE reset_token = '$token' AND reset_token_expiry > NOW()";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $email = $user['email'];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!empty($_POST['password'])) {
                $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

                $update_sql = " UPDATE users SET password = '$newPassword', reset_token = NULL, reset_token_expiry = NULL 
                               WHERE email = '$email' ";
                if (mysqli_query($conn, $update_sql)) {
                    echo "<script>
                        alert('✅ Password reset successfully!');
                        window.location.href = 'login.php';
                    </script>";
                    exit();
                } else {
                    $message = "❌ Error updating password.";
                }
            } else {
                $message = "❌ Password cannot be empty.";
            }}} 
        else {
        echo "<script>
            alert('❌ Invalid or expired token.');
            window.location.href = 'login.php';
        </script>";
        exit();
    }} 
    else {
    echo "<script>
        alert('❌ No token provided.');
        window.location.href = 'login.php';
    </script>";
    exit();
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    <?php if (!empty($email)) { ?>
        <form method="POST">
            <input type="password" name="password" required placeholder="Enter new password">
            <button type="submit">Update Password</button>
        </form>
    <?php } ?>
</body>
</html>