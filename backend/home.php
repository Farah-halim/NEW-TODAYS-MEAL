<?php
session_start();

if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>

    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <?php if ($_SESSION['user_role'] === 'caterer') : ?>
                <li><a href="caterer/my_food.php">My Food</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! You are on our home page.</h1>
    </div>

</body>
</html>
