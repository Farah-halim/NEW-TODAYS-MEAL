<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Today's Meal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="login.css"> 

</head>
    <body>

    <?php include 'nav3.php'; ?>

    <main>
        <section class="left-section">
            <img src="food.jpg" alt="Delicious food">
            <h2> Welcome Back!</h2>
            <p class="highlight-text">
                Log in to explore delicious homemade meals from the best home cooks in Egypt.
            </p>
        </section>

        <section class="right-section">
            <h2>Login to Your Account</h2>
            <form>
                <label>Email address</label>
                <input type="email" placeholder="Enter your email">
                <label>Password</label>
                <input type="password" placeholder="Enter your password">
                <div class="checkbox-container">
                    <label><input type="checkbox"> Remember me</label>
                    <a href="forgotpassword.php" class="forgot-password">Forgot password?</a>
                    </div>
                <button type="submit" class="submit-btn">Login</button>
                <p class="signup-link">Don't have an account? <a href="register.php">Sign up</a></p>
            </form>
        </section>
    </main>
    <script src="login.js"></script> 

<?php include 'footer.php'; ?> 
</body>
</html>
