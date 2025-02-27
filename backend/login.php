<?php
session_start();
include("../DB_connection.php"); 

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $error = array();

    if (empty($email)) {
        array_push($error, "Email is required");
    }

    if (empty($password)) {
        array_push($error, "Password is required");
    }

    if (count($error) == 0) {
        $sql = "SELECT user_id, name, role, password, is_approved FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                if ($user['role'] === 'caterer' && $user['is_approved'] == 0) {
                    echo "Your account is under review. Please wait for admin approval.";
                } 
                else {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    header("Location: home.php");
                    exit();
                }
            } 
            else {
                echo "Incorrect email or password.";
            }
        } 
        else {
            echo "Incorrect email or password.";
        }
    }
}

mysqli_close($conn);
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
            <a href="register.php"> New user? Register Now </a>

        </div>
     
    </div>
</div>

</body>
</html>