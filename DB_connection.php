<?php
// Database Configuration
$host = "localhost";  
$dbname = "today_s_meal_10";  
$username = "root";  
$password = "";  

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");
?>