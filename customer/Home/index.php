<?php
session_start();
require_once("..\DB_connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: \NEW-TODAYS-MEAL\Register&Login\login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if (isset($_GET['ajax_search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    $category_query = "SELECT * FROM category WHERE c_name LIKE '%$search_term%'";
    $category_result = mysqli_query($conn, $category_query);
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($categories);
    exit();
}

$search_term = '';
if (isset($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    $category_query = "SELECT * FROM category WHERE c_name LIKE '%$search_term%'";
} else {
    $category_query = "SELECT * FROM category";
}
$category_result = mysqli_query($conn, $category_query);
$categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Meal - Home Food Delivery</title>
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .category-grid {transition: all 0.3s ease;}
        .category-button {transition: transform 0.2s ease, opacity 0.3s ease;}
        .search-bar {position: relative;}
        .search-loader {position: absolute;right: 10px;top: 50%;transform: translateY(-50%);display: none;}
        .search-bar.loading .search-loader {display: block;}
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .spinner {animation: spin 1s linear infinite;}
    </style>
</head>

<body>
    <?php include '..\global\navbar\navbar.php'; ?>
    <div class="home-page">
        <main>
            <section id="top" class="hero">
                <div class="hero-content">
                    <h1>Order Homemade Food</h1>
                    <p>Customize your meals, create weekly plans, and enjoy delicious home-cooked food delivered to your doorstep.</p>
                    <button class="cta-button" id="exploreMealsBtn">Explore Meals</button>
                </div>
                <div class="hero-image animated"></div>
            </section>

            <section id="search-section" class="search-section">
                <form id="searchForm" method="GET">
                    <div class="search-bar" id="searchBar">
                        <img src="https://img.icons8.com/material-outlined/24/734338/search--v1.png" alt="search"/>
                        <input type="text" name="search" id="searchInput" placeholder="Search for meal categories..." 
                               value="<?php echo htmlspecialchars($search_term) ?>"/>
                        <div class="search-loader">
                            <i class="fas fa-spinner spinner"></i>
                        </div>
                    </div>
                </form>
            </section>

            <section class="featured-categories">
                <h2 id="categoriesTitle"><?php echo $search_term ? 'Search Results' : 'Featured Categories'; ?></h2>
                <div id="categoriesContainer">
                    <?php if (empty($categories)): ?>
                        <p class="no-results">No categories found matching your search.</p>
                    <?php else: ?>
                        <div class="category-grid" id="categoryGrid">
                            <?php foreach ($categories as $category): ?>
                                <a href="\NEW-TODAYS-MEAL\customer\Show_Caterers\index.php?cat_id=<?php echo $category['cat_id'] ?>">
                                    <button class="category-button">
                                        <?php if ($category['category_photo']): ?>
                                            <div class="category-image" style="background-image: url('<?php echo htmlspecialchars($category['category_photo']) ?>')"></div>
                                        <?php else: ?>
                                            <div class="category-image image-default"></div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($category['c_name']) ?></span>
                                    </button>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section id="weekly-meal-plans" class="meal-plans">
                <h2>Tailored to Fit Your Needs</h2>
                <p>Whether it's a birthday, a busy work week, or specific dietary needs, we've got you covered with flexible meal solutions.</p>
                
                <div class="plan-cards">
                    <div class="plan-card">
                        <div class="icon-circle">
                            <img src="https://c.animaapp.com/m8ipwas4EnbDUS/img/frame.svg" alt="Schedule icon" />
                        </div>
                        <h3>Event Catering</h3>
                        <p>Planning a birthday or gathering? Customize meals for your guests, add cake requests, and upload inspiration photos.</p>
                        <ul>
                            <li>Set guest count</li>
                            <li>Request custom cakes</li>
                            <li>Upload reference images</li>
                        </ul>
                    </div>
                    
                    <div class="plan-card popular">
                        <div class="popular-tag">Most Popular</div>
                        <div class="icon-circle">
                            <img src="https://c.animaapp.com/m8ipwas4EnbDUS/img/frame.svg" alt="Customize icon" />
                        </div>
                        <h3>Scheduled Orders</h3>
                        <p>Choose your preferred delivery time from the cart. Great for professionals, moms, and meal planners.</p>
                        <ul>
                            <li>Choose from 50+ dishes</li>
                            <li>Schedule in checkout</li>
                            <li>Set day & time</li>
                            <li>Modify anytime</li>
                        </ul>
                    </div>
                    
                    <div class="plan-card">
                        <div class="icon-circle">
                            <img src="https://c.animaapp.com/m8ipwas4EnbDUS/img/frame.svg" alt="Flexible icon" />
                        </div>
                        <h3>Allergy-Friendly Options</h3>
                        <p>Filter meals based on dietary needs like gluten-free, nut-free, or low-calorie to eat safely and happily.</p>
                        <ul>
                            <li>Vegan & Vegetarian</li>
                            <li>Gluten/Nut/Dairy-Free</li>
                            <li>Low-fat & sugar-free meals</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="special-event-catering" class="catering">
                <div class="catering-image"></div>
                <div class="catering-content">
                    <h2>Birthday Party Catering</h2>
                    <p>Make your birthday celebration special! We offer custom birthday catering packages including personalized cakes made exactly as you envision.</p>
                    
                    <div class="catering-features">
                        <div class="feature">
                            <img src="https://img.icons8.com/ios-filled/50/a35831/birthday-cake.png" alt="Custom cake" />
                            <h4>Custom Cakes</h4>
                            <p>Send us a photo, we'll create it</p>
                        </div>
                        <div class="feature">
                            <img src="https://img.icons8.com/ios-filled/50/a35831/party-baloons.png" alt="Party packages" />
                            <h4>Party Packages</h4>
                            <p>Complete birthday solutions</p>
                        </div>
                        <div class="feature">
                            <img src="https://img.icons8.com/ios-filled/50/a35831/food-and-wine.png" alt="Menu options" />
                            <h4>Party Menu</h4>
                            <p>Kid-friendly options available</p>
                        </div>
                    </div>
                    <button>Plan Your Party</button>
                </div>
            </section>
        </main>
    </div>
    <script src='script.js'></script>
    <?php include '..\global\footer\footer.php'; ?>
</body>
</html>