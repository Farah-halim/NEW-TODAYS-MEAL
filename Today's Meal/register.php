<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Today's Meal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="register.css"> 
</head>
    <body>

    <?php include 'nav3.php'; ?>

    <main>
        <section class="left-section">
        <img src="food.jpg" alt="Delicious food">
        <h2>Join Our Culinary Community</h2>
            <p class="highlight-text">
                Connect with the best home cooks in Egypt, order delicious homemade meals, or become a delivery partner.
            </p>
        </section>

        <section class="right-section">
            <h2>Create an Account</h2>
            <div class="account-type">
                <button class="active" data-type="customer">Customer</button>
                <button data-type="caterer">Caterer</button>
                <button data-type="delivery">Delivery Man</button>
            </div>
            <form>
                <label>Full Name</label>
                <input type="text" placeholder="Enter your full name">

                <label>Email address</label>
                <input type="email" placeholder="Enter your email">

                <label>Phone Number</label>
                <input type="tel" placeholder="Enter your phone number">

                <label>Password</label>
                <input type="password" placeholder="Create a password">

                <label>Confirm Password</label>
                <input type="password" placeholder="Confirm your password">

                <!-- Caterer Fields -->
                <div id="caterer-fields" class="hidden">
                    <label>Years of Experience</label>
                    <input type="number" placeholder="Enter years of experience">

                    <label>Category of Food</label>
                    <input type="text" placeholder="E.g., Desserts, Egyptian Cuisine, Healthy Meals">

                    <label>Brief on what you serve</label>
                    <textarea placeholder="Describe your meals and specialties"></textarea>

                    <label>Upload Logo</label>
                    <input type="file" accept="image/*">

                    <label>Upload National ID</label>
                    <input type="file" accept="image/*,application/pdf">

                    <label>Location</label>
                    <input type="text" placeholder="Enter your location">
                </div>

                <!-- Delivery Man Fields -->
                <div id="delivery-fields" class="hidden">
                    <label>Upload National ID</label>
                    <input type="file" accept="image/*,application/pdf">

                    <label>Upload Driver's License</label>
                    <input type="file" accept="image/*,application/pdf">

                    <label>Date of Birth</label>
                    <input type="date">
                </div>
                <div class="terms-container">
                    <input type="checkbox" id="terms">
                    <label for="terms">I agree to the terms and conditions</label>
                </div>


                <button type="submit" class="submit-btn">Create Account</button>
                <p class="login-link">
    Already have an account? <a href="login.php">Log in</a> </p>
            </form>
        </section>
    </main>
    <script src="register.js"></script> 
    <?php include 'footer.php'; ?> 

</body>
</html>
