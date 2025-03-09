<?php
session_start();

if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['user_role'] !== 'caterer') {
    die("Sorry, only caterers can access this page!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../frontend/css/profile_icon.css">
</head>
<body>

<div class="top-bar">
    <div class="nav-links">
        <a href="my_food.php">My Food</a>
    </div>

    <div class="profile-container" tabindex="0">
        <i class="fa-solid fa-user-circle profile-icon"></i>
        <div class="dropdown">
            <a href="../settings.php">Account Settings</a>
            <a href="../settings.php#change-password">Change Password</a>
            <a href="../logout.php" style="color: red;">Logout</a>
        </div>
    </div>
</div>

</body>
</html>

