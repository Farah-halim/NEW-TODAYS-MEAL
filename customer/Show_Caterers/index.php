<?php
session_start();
require_once('../DB_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: \NEW-TODAYS-MEAL\Register&Login\login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if (isset($_GET['reset'])) {
    header("Location: index.php");
    exit();
}
$search_term = '';
$category_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : '';
$min_rating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0; 
$custom_filter = isset($_GET['custom_filter']) ? $_GET['custom_filter'] : 'all';

if (isset($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
}

$query = "SELECT cko.user_id, cko.business_name, cko.average_rating, cko.start_year, cko.customized_orders, cko.years_of_experience
          FROM cloud_kitchen_owner cko
          LEFT JOIN cloud_kitchen_specialist_category cksc ON cko.user_id = cksc.cloud_kitchen_id
          WHERE cko.is_approved = 1";

if ($category_id) {
    $query .= " AND cksc.cat_id = $category_id";
}

if ($search_term) {
    $query .= " AND cko.business_name LIKE '%$search_term%'";
}
$query .= " AND cko.average_rating >= $min_rating";

if ($custom_filter === 'custom') {
    $query .= " AND cko.customized_orders = 1";
} elseif ($custom_filter === 'non-custom') {
    $query .= " AND cko.customized_orders = 0";
}
$query .= " GROUP BY cko.user_id";

$result = mysqli_query($conn, $query);
$cloud_kitchens = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Kitchen - Food Delivery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
      <?php include '..\global\navbar\navbar.php'; ?>

    <div class="container">
        <div class="nav-section" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <a href="\NEW-TODAYS-MEAL\customer\Home\index.php" class="nav-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m12 19-7-7 7-7"/>
                </svg>
                <span>Back to home</span>
            </a>
            <a href="?reset=1" class="nav-link" style="margin-left: auto; background-color: #f5e0c2; padding: 8px 12px; border-radius: 6px; transition: background-color 0.3s;">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Reset Filters</span>
            </a>
        </div>
        <h1 class="page-title">Cloud Kitchens</h1>

        <form method="GET">
            <div class="search-card">
                <div class="search-container">
                    <?php if ($category_id): ?>
                        <input type="hidden" name="cat_id" value="<?php echo $category_id; ?>">
                    <?php endif; ?>
                    <?php if ($custom_filter !== 'all'): ?>
                        <input type="hidden" name="custom_filter" value="<?php echo $custom_filter; ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="search-input" placeholder="Search cloud kitchen..." 
                          value="<?php echo htmlspecialchars($search_term); ?>">
                    <div class="rating-filters">
                        <span class="filter-label">Min Rating:</span>
                        <button type="submit" name="rating" value="0" class="rating-btn <?php echo $min_rating == 0 ? 'active' : ''; ?>">All Stars</button>
                        <button type="submit" name="rating" value="1" class="rating-btn <?php echo $min_rating == 1 ? 'active' : ''; ?>">⭐ 1+</button>
                        <button type="submit" name="rating" value="2" class="rating-btn <?php echo $min_rating == 2 ? 'active' : ''; ?>">⭐ 2+</button>
                        <button type="submit" name="rating" value="3" class="rating-btn <?php echo $min_rating == 3 ? 'active' : ''; ?>">⭐ 3+</button>
                        <button type="submit" name="rating" value="4" class="rating-btn <?php echo $min_rating == 4 ? 'active' : ''; ?>">⭐ 4+</button>
                        <button type="submit" name="rating" value="5" class="rating-btn <?php echo $min_rating == 5 ? 'active' : ''; ?>">⭐ 5</button>
                    </div> 
                </div>
            </div>
        </form>
      
        <div class="category-tabs" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 32px;">
            <a href="?<?php echo http_build_query(array_merge($_GET, ['custom_filter' => 'all'])); ?>" 
              class="category-btn <?php echo $custom_filter === 'all' ? 'active' : ''; ?>" 
              style="padding: 12px 24px; border: none; border-radius: 8px; background: <?php echo $custom_filter === 'all' ? '#E67E22' : '#D2B48C'; ?>; color: <?php echo $custom_filter === 'all' ? 'white' : '#8B4513'; ?>; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block;">All</a>
            
            <a href="?<?php echo http_build_query(array_merge($_GET, ['custom_filter' => 'custom'])); ?>" 
              class="category-btn <?php echo $custom_filter === 'custom' ? 'active' : ''; ?>" 
              style="padding: 12px 24px; border: none; border-radius: 8px; background: <?php echo $custom_filter === 'custom' ? '#E67E22' : '#D2B48C'; ?>; color: <?php echo $custom_filter === 'custom' ? 'white' : '#8B4513'; ?>; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block;">Provide Custom Orders</a>
            
            <a href="?<?php echo http_build_query(array_merge($_GET, ['custom_filter' => 'non-custom'])); ?>" 
              class="category-btn <?php echo $custom_filter === 'non-custom' ? 'active' : ''; ?>" 
              style="padding: 12px 24px; border: none; border-radius: 8px; background: <?php echo $custom_filter === 'non-custom' ? '#E67E22' : '#D2B48C'; ?>; color: <?php echo $custom_filter === 'non-custom' ? 'white' : '#8B4513'; ?>; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block;">Provide Standard Orders</a>
        </div>

        <div class="restaurant-grid" id="restaurantGrid">
            <?php if (empty($cloud_kitchens)): ?>
                <div class="no-results">
                    <p>No cloud kitchens found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($cloud_kitchens as $kitchen): ?>
                    <div class="restaurant-card" data-custom="<?php echo $kitchen['customized_orders'] ? '1' : '0'; ?>">
                        <div class="restaurant-image">
                            <img src="caterer.jpg" alt="<?php echo htmlspecialchars($kitchen['business_name']); ?>">
                            <div class="rating-badge">
                                <svg class="star-icon" viewBox="0 0 24 24" fill="white">
                                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
                                </svg>
                                <span><?php echo number_format($kitchen['average_rating'], 1); ?></span>
                            </div>
                        </div>
                        <div class="restaurant-content">
                            <h3 class="restaurant-name"><?php echo htmlspecialchars($kitchen['business_name']); ?></h3>
                            <div class="restaurant-info">
                                <svg class="clock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12,6 12,12 16,14"/>
                                </svg>
                                <span><?php echo $kitchen['years_of_experience']; ?></span>
                            </div>
                            <?php if ($kitchen['customized_orders']): ?>
                              <a href="\NEW-TODAYS-MEAL\customer\Custom_Order\custom-order.php?kitchen_id=<?php echo $kitchen['user_id']; ?>" class="custom-order-btn">
                                Request Custom Order </a>
                            <?php endif; ?>
                            <a href="\NEW-TODAYS-MEAL\customer\Show Menu_Codes\index.php?kitchen_id=<?php echo $kitchen['user_id']; ?>" class="view-menu-btn">View Menu</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
          <?php include '..\global\footer\footer.php'; ?>
    <script src='script.js'> </script>
</body>
</html>