
<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // For demo purposes, using admin/admin123
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: c/c/admin_dashboard.php');
    } else {
        header('Location: index.php?error=1');
    }
}
?>
