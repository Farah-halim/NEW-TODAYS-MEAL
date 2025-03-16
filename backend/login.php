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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../frontend/css/login.css"> 
</head>

<body>
    <?php include 'nav3.php'; ?>
    <main>
        <section class="left-section">
            <img src="../images/food.jpg" alt="Delicious food">
            <h2> Welcome Back!</h2>
            <p class="highlight-text"> Log in to explore delicious homemade meals from the best home cooks in Egypt. </p>
        </section>

        <section class="right-section">
            <h2>Login to Your Account</h2>
            <form action="login.php" method="post">
                <label>Email address</label>
                <input type="email" placeholder="Enter your email" name="email" id="email" required>
                <label>Password</label>
                <input type="password" placeholder="Enter your password" name="password" id="password" required>
                <div class="checkbox-container">
                    <label> <input type="checkbox"> Remember me</label>
                    <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                    </div>
                <button type="submit" class="submit-btn" name="submit">Login</button>
                <p class="signup-link"> Don't have an account? <a href="register.php">Sign up</a></p>
            </form>
        </section>
    </main>
<?php include 'footer.php'; ?> 
</body>
</html>