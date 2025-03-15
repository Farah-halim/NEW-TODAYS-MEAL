<?php
// Database Configuration
$host = "localhost";  // Change if using a remote database
$dbname = "today's meal";  // Replace with your actual database name
$username = "root";  // Default for XAMPP (change if needed)
$password = "";  // Default for XAMPP (change if you have set a password)

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");
?>
