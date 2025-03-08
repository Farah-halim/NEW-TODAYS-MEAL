<?php
session_start();
include("../../DB_connection.php");

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'caterer') {
    die("Unauthorized access");
}
$caterer_id = $_SESSION['user_id'];

// Fetch the caterer's food items along with their categories
$sql = "
    SELECT f.*, GROUP_CONCAT(c.category_name SEPARATOR ', ') AS categories
    FROM food f
    LEFT JOIN food_categories fc ON f.food_id = fc.food_id
    LEFT JOIN categories c ON fc.category_id = c.category_id
    WHERE f.caterer_id = '$caterer_id'
    GROUP BY f.food_id
";
$result = $conn->query($sql);  ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Food</title>
    <link rel="stylesheet" href="../../frontend/css/my_food.css">
</head>
<body>
    <nav>
        <a href="add_food.php">Add Food</a>
    </nav>
    <div class="container">
        <h2>My Food Items</h2>
        <div class="food-container">
            <?php if ($result->num_rows > 0): ?> 
                <?php while ($row = $result->fetch_assoc()): ?>  <!-- Loop through each food item in the result set -->
                    <div class="food-card">
                        <img src="../../<?php echo $row['image']; ?>" alt="Food Image">
                        <div class="food-info">
                            <span class="food-title"><?php echo $row['title']; ?></span>
                            <span class="food-price"><?php echo $row['price']; ?> EGP</span>
                        </div>
                        <p> <?php echo $row['description']; ?> </p>

                        <div class="tags">
                            <?php $categories = explode(", ", $row['categories']);
                            foreach ($categories as $category) {
                                echo "<p class='tag'> $category </p>"; 
                            }
                            ?>
                        </div>
                    </div>
                    <?php endwhile; else: ?>
                        <p>No food items found.</p>
                        <?php endif; ?>
        </div>
    </div>
</body>
</html>