<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Today's Meal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="login.css"> 
    <link rel="stylesheet" href="meal.css"> 
</head>
    <body>

    <?php include 'nav3.php'; ?>

    <main>
        <section class="right-section">
            <h2>Reset Your Password</h2>
            <p class="highlight-text">Enter your email address below, and we'll send you a link to reset your password.</p>
            <form>
                <label>Email address</label>
                <input type="email" placeholder="Enter your email" required>
                <button type="submit" class="submit-btn">Send Reset Link</button>
                <p class="signup-link">Remembered your password? <a href="login.php">Login</a></p>
            </form>
        </section>
    </main>
        <script src="forgotpassword.js"></script> 

<?php include 'footer.php'; ?> 
</body>
</html>

