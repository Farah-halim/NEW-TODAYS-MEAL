<?php
session_start();
require '../DB_connection.php'; // Database connection

if (isset($_SESSION['error_message'])) {
    echo "<p style='color: red; font-weight: bold;'>" . $_SESSION['error_message'] . "</p>";
    unset($_SESSION['error_message']); // Clear the message after displaying it
}


if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo "Email and password are required.";
        exit();
    }

    // Fetch user from `users` table
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, username, role, password, is_approved FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            if ($user['role'] === 'caterer' && $user['is_approved'] == 0) {
                echo "Your account is under review. Please wait for admin approval.";
            } else {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                if ($_SESSION['user_role'] === 'caterer') {
                    header("Location: caterer/home.php");
                } else {
                    header("Location: customer/home.php");
                }
                exit();
            }
        } else {
            echo "Incorrect email or password.";
        }
    } else {
        echo "Incorrect email or password.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
<div class="box">
    <div class="container">
        <div class="content">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <input type="email" placeholder="Email" name="email" id="email" required>
                <input type="password" placeholder="Password" name="password" id="password" required>
                <button type="submit" class="btn-contact" name="submit" > Submit </button>
            </form>
            <a href="register.php"> New user? Register Now </a> <br>
            <a href="forgot_password.php"> Forgot password </a>
        </div>
    </div>
</div>
</body>
</html>