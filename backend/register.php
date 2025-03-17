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

    <?php 
    include '../DB_connection.php';
    include 'nav3.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['customer_address']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Get user role based on form data
        if (isset($_POST['experience_level']) && !empty($_POST['experience_level'])) {
            $role = 'caterer';
            $experience = mysqli_real_escape_string($conn, $_POST['experience_level']);
            $national_id = mysqli_real_escape_string($conn, $_POST['national_id']);
        } elseif (isset($_POST['delivery_national_id']) && !empty($_POST['delivery_national_id'])) {
            $role = 'delivery';
            $national_id = mysqli_real_escape_string($conn, $_POST['delivery_national_id']);
            $driver_license = mysqli_real_escape_string($conn, $_POST['license_number']);
        } else {
            $role = 'customer';
        }

        // Check if email already exists
        $check_email = "SELECT email FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $check_email);
        
        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('Email already exists!');</script>";
        } else {
            // Base query for all users
            if ($role == 'caterer') {
                $category_id = mysqli_real_escape_string($conn, $_POST['category']);
                $sql = "INSERT INTO users (name, email, password, phone, address1, role, is_approved, national_id, years_of_experience, category_id) 
                       VALUES ('$name', '$email', '$password', '$phone', '$address', '$role', 0, '$national_id', '$experience', '$category_id')";
            } elseif ($role == 'delivery') {
                $sql = "INSERT INTO users (name, email, password, phone, address1, role, is_approved, national_id, driver_license) 
                       VALUES ('$name', '$email', '$password', '$phone', '$address', '$role', 0, '$national_id', '$driver_license')";
            } else {
                $sql = "INSERT INTO users (name, email, password, phone, address1, role, is_approved) 
                       VALUES ('$name', '$email', '$password', '$phone', '$address', '$role', 1)";
            }

            if (mysqli_query($conn, $sql)) {
                if ($role == 'caterer' || $role == 'delivery') {
                    echo "<script>alert('Registration successful! Please wait for admin approval.'); window.location.href='login.php';</script>";
                } else {
                    echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
                }
            } else {
                echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
            }
        }
    }
    ?>

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
                    <select name="experience_level" class="styled-select" required>
                        <option value="" disabled selected>Select your experience level</option>
                        <option value="No Experience">No Experience</option>
                        <option value="Beginner (0-1 years)">Beginner (0-1 years)</option>
                        <option value="Intermediate (2-3 years)">Intermediate (2-3 years)</option>
                        <option value="Advanced (4-5 years)">Advanced (4-5 years)</option>
                        <option value="Expert (6+ years)">Expert (6+ years)</option>
                    </select>

                    <label>Category of Food</label>
                    <select name="category" class="styled-select" required>
                        <option value="" disabled selected>Select food category</option>
                        <?php
                        $category_query = "SELECT category_id, category_name FROM categories";
                        $category_result = $conn->query($category_query);
                        while($category = $category_result->fetch_assoc()) {
                            echo "<option value='" . $category['category_id'] . "'>" . htmlspecialchars($category['category_name']) . "</option>";
                        }
                        ?>
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