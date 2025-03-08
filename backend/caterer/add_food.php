<?php
session_start();
include("../../DB_connection.php");

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'caterer') {
    die("Unauthorized access");
}

$categories_query = "SELECT * FROM categories";
$categories_result = $conn->query($categories_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $caterer_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if (empty($_POST['categories'])) {
        die("Please select at least one category.");
    }
    $selected_categories = $_POST['categories'];

    $target_dir = "../../uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowed_types = ["jpg", "jpeg", "png","gif"];
    if (!in_array($imageFileType, $allowed_types)) {
        die("Only JPG, JPEG,gif and PNG files are allowed.");
    }

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = "uploads/" . $image_name;

        $sql = "INSERT INTO food (caterer_id, title, price, description, image) 
                VALUES ('$caterer_id', '$title', '$price', '$description', '$image_path')";

        if ($conn-> query($sql) === TRUE) {
            $food_id = $conn->insert_id; 

            foreach ($selected_categories as $category_id) {
                $conn-> query("INSERT INTO food_categories (food_id, category_id) VALUES ('$food_id', '$category_id')");
            }
            header("Location: my_food.php"); 
            exit();} 
        else { echo "Error: " . $conn->error; }} 

    else { echo "Error uploading image."; } }
?>

<h2>Add New Food</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Food Title" required><br>

    <label> Select Categories: </label> <br>
    <?php while ($row = $categories_result->fetch_assoc()): ?>
        <input type="checkbox" name="categories[]" value="<?php echo $row['category_id']; ?>">
        <?php echo htmlspecialchars($row['category_name']); ?> <br>
    <?php endwhile; ?>

    <input type="number" name="price" placeholder="Price" step="0.01" required> <br>
    <textarea name="description" placeholder="Food Description" required> </textarea> <br>
    <input type="file" name="image" required> <br> 
    <button type="submit"> Add Food </button>
</form>
