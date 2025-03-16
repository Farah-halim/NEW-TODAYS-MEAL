<?php
session_start();
require '../DB_connection.php'; // Database connection

if (isset($_POST['register'])) {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $phone = $_POST["phone"];
    $address1 = $_POST["address1"];
    $address2 = $_POST["address2"];
    $role = $_POST["role"];
    $isApproved = ($role === 'customer') ? 1 : 0; // Default approval for customers

    // Insert into `users` table
    $sql = "INSERT INTO users (first_name, last_name, username, email, password, phone, address1, address2, role, is_approved) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $first_name, $last_name, $username, $email, $password, $phone, $address1, $address2, $role, $isApproved);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../frontend/css/register.css">
</head>
<body>
    <h2>User Registration</h2>
    <form action="register.php" method="POST">
        <label>fName:</label>
        <input type="text" name="first_name" required><br>

        <label>lName:</label>
        <input type="text" name="last_name" required><br>

        <label>uName:</label>
        <input type="text" name="username" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <label>Phone:</label>
        <input type="number" name="phone" required><br>

        <label>Address:</label>
        <input type="text" name="address1" required><br>

        <label>Address:</label>
        <input type="text" name="address2" required><br>

        <label>Role:</label><br>
        <input type="radio" name="role" value="customer" required> Customer<br>
        <input type="radio" name="role" value="caterer" required> Caterer<br>

        <button type="submit" name="register">Register</button> 
    </form>
</body>
</html>
