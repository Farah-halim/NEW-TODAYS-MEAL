<?php 
session_start();
include("../DB_connection.php"); 

if (isset($_POST['register'])) { 
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); 
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $role = $_POST["role"];

    // Only caterers require admin approval
    $isApproved = ($role === 'customer') ? 1 : 0; 

    $sql = "INSERT INTO users (name, email, password, phone, role, is_approved) 
        VALUES ('$name', '$email', '$password','$phone','$role', $isApproved)";

  
    if ($conn->query($sql) === TRUE) {
        header("Location:login.php");
        exit(); 
    }
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
        <label>Name:</label>
        <input type="text" name="name" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <label>Phone:</label>
        <input type="number" name="phone" required><br>

        <label>Address:</label>
        <input type="text" name="address" required><br>

        <label>Role:</label><br>
        <input type="radio" name="role" value="customer" required> Customer<br>
        <input type="radio" name="role" value="caterer" required> Caterer<br>

        <button type="submit" name="register">Register</button> 
    </form>
</body>
</html>
