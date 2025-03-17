<?php
session_start();
include("../DB_connection.php"); 

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $error = array();

    if (empty($email) || empty($password)) {
        array_push($error, "Email and password cannot be empty.");}

    if (count($error) == 0) {
        $sql = "SELECT user_id, name, role, password, is_approved FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                if (($user['role'] === 'caterer' || $user['role'] === 'delivery') && $user['is_approved'] == 0) {
                    echo '<div class="alert alert-info text-center" style="max-width: 400px; margin: 20px auto; padding: 15px; border-radius: 8px; background-color: #e1f5fe; color: #01579b;">
                            <i class="fas fa-clock" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p style="margin: 0;">Your account is under review. Please wait for admin approval.</p>
                          </div>';
                } 
                else {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                
                    if ($_SESSION['user_role'] === 'caterer') {
                        header("Location: caterer/home.php");
                        exit();} 
                    else {
                        header("Location: customer/profile-info.php"); 
                        exit();}
                }} 
            else {
                echo "Incorrect email or password.";
            }} 
        else {
            echo "Incorrect email or password.";
        }}}
mysqli_close($conn);
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