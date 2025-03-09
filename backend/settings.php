<?php
session_start();
include("../DB_connection.php"); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$query = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $update_query = "UPDATE users SET name = '$name', email = '$email', address = '$address' WHERE user_id = $user_id";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['user_name'] = $name;
        echo "<script>alert('Profile updated successfully!');</script>";

        // Redirect based on user role
        if ($_SESSION['user_role'] === 'caterer') {
            echo "<script>window.location.href = 'caterer/home.php';</script>";
        } elseif ($_SESSION['user_role'] === 'customer') {
            echo "<script>window.location.href = 'customer/home.php';</script>";
        } else {
            echo "<script>window.location.href = 'index.php';</script>";
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_query = "UPDATE users SET password = '$hashed_password' WHERE user_id = $user_id";

            if (mysqli_query($conn, $update_password_query)) {
                echo "<script>alert('Password changed successfully!');</script>";

                // Redirect based on user role
                if ($_SESSION['user_role'] === 'caterer') {
                    echo "<script>window.location.href = 'caterer/home.php';</script>";
                } elseif ($_SESSION['user_role'] === 'customer') {
                    echo "<script>window.location.href = 'customer/home.php';</script>";
                }
            }
        } else {
            echo "<script>alert('New passwords do not match!');</script>";
        }
    } else {
        echo "<script>alert('Current password is incorrect!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="../frontend/css/settings.css">
</head>
<body>

<div class="settings-page">
    <div class="settings-container">
        <h1 class="page-title">Account Settings</h1>

        <div class="settings-section">
            <h2 class="settings-title">General Information</h2>
            <form method="POST">
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label>Address:</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>

                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </div>

        <a href="#change-password-section" class="go-to-section">Change Password</a>

        <div class="settings-section" id="change-password-section">
            <h2 class="settings-title">Change Password</h2>
            <form method="POST">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>

                <label>New Password:</label>
                <input type="password" name="new_password" required>

                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password" required>

                <button type="submit" name="change_password">Change Password</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
