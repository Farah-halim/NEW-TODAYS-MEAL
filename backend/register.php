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
        <img src="../images/food.jpg" alt="Delicious food">
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
            <form method="POST" action="" enctype="multipart/form-data">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter your full name" required>

                <label>Email address</label>
                <input type="email" name="email" placeholder="Enter your email" required>

                <label>Phone Number</label>
                <input type="tel" name="phone" placeholder="Enter your phone number" required>

                <label>Address</label>
                <input type="text" name="customer_address" placeholder="Enter your full address" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>

                <!-- Caterer Fields -->
                <div id="caterer-fields" class="hidden">
                    <label>Years of Experience</label>
                    <select name="experience" class="styled-select" required>
                        <option value="" disabled selected>Select your experience level</option>
                        <option value="No Experience">No Experience</option>
                        <option value="Beginner">Beginner (0-1 years)</option>
                        <option value="Intermediate">Intermediate (2-3 years)</option>
                        <option value="Advanced">Advanced (4-5 years)</option>
                        <option value="Expert">Expert (6+ years)</option>
                    </select>

                    <label>Category of Food</label>
                    <select name="category" class="styled-select" required>
                        <option value="" disabled selected>Select food category</option>
                        <option value="Desserts">Desserts</option>
                        <option value="Drinks">Drinks</option>
                        <option value="Fast Food">Fast Food</option>
                        <option value="Italian">Italian</option>
                        <option value="Seafood">Seafood</option>
                        <option value="Vegetarian">Vegetarian</option>
                    </select>

                    <label>National ID Number</label>
                    <input type="text" name="national_id" placeholder="Enter your National ID number" pattern="\d*" maxlength="14" required>
                </div>

                <!-- Delivery Man Fields -->
                <div id="delivery-fields" class="hidden">
                    <label>National ID Number</label>
                    <input type="text" name="delivery_national_id" placeholder="Enter your National ID number" pattern="\d*" maxlength="14" required>

                    <label>Driver's License Number</label>
                    <input type="text" name="license_number" placeholder="Enter your driver's license number" pattern="\d*" maxlength="14" required>
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
    <?php include 'footer.php'; ?> 
    <script src="register.js"></script>
</body>
</html>