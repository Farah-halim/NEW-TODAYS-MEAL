<?php
session_start();
require_once('../DB_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: \NEW-TODAYS-MEAL\Register&Login\login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['kitchen_id']) || empty($_GET['kitchen_id'])) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - No Kitchen Selected</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="container">
            <div class="error-message">
                <h2>Oops! No Kitchen Selected</h2>
                <p>Please select a kitchen from our list to view their menu.</p>
                <a href="../3-Show Caterers Codes/index.php">Browse Kitchens</a>
            </div>
        </div>
    </body>
    </html>';
    exit();
}

$kitchen_id = (int)$_GET['kitchen_id'];

// Get cloud kitchen ID from URL
$kitchen_id = isset($_GET['kitchen_id']) ? (int)$_GET['kitchen_id'] : 0;

// Initialize variables
$search_term = '';
$price_range = 'all';
$current_category = 'all';

// Get search term if provided
if (isset($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
}

// Get price range if provided
if (isset($_GET['price'])) {
    $price_range = mysqli_real_escape_string($conn, $_GET['price']);
}

// Get category if provided
if (isset($_GET['category'])) {
    $current_category = mysqli_real_escape_string($conn, $_GET['category']);
}

// Get cloud kitchen details
$kitchen_query = "SELECT cko.business_name, cko.customized_orders 
                  FROM cloud_kitchen_owner cko 
                  WHERE cko.user_id = $kitchen_id AND cko.is_approved = 1";
$kitchen_result = mysqli_query($conn, $kitchen_query);
$kitchen = mysqli_fetch_assoc($kitchen_result);

// Get meals for this cloud kitchen with their categories and dietary tags
$meals_query = "SELECT m.meal_id, m.name, m.description, m.price, m.photo, m.stock_quantity,
                       GROUP_CONCAT(DISTINCT c.c_name SEPARATOR ', ') AS categories,
                       GROUP_CONCAT(DISTINCT c.cat_id SEPARATOR ',') AS category_ids,
                       GROUP_CONCAT(DISTINCT dt.tag_name SEPARATOR ', ') AS dietary_tags,
                       LOWER(GROUP_CONCAT(DISTINCT dt.tag_name SEPARATOR '|||')) AS dietary_tags_search
                FROM meals m
                LEFT JOIN meal_category mc ON m.meal_id = mc.meal_id
                LEFT JOIN category c ON mc.cat_id = c.cat_id
                LEFT JOIN meal_dietary_tag mdt ON m.meal_id = mdt.meal_id
                LEFT JOIN dietary_tags dt ON mdt.tag_id = dt.tag_id
                WHERE m.cloud_kitchen_id = $kitchen_id AND m.visible = 1
                GROUP BY m.meal_id";
$meals_result = mysqli_query($conn, $meals_query);
$meals = mysqli_fetch_all($meals_result, MYSQLI_ASSOC);

// Get all unique categories this cloud kitchen specializes in
$specialized_categories_query = "SELECT DISTINCT c.cat_id, c.c_name 
                                FROM category c
                                JOIN cloud_kitchen_specialist_category cksc ON c.cat_id = cksc.cat_id
                                WHERE cksc.cloud_kitchen_id = $kitchen_id";
$specialized_categories_result = mysqli_query($conn, $specialized_categories_query);
$specialized_categories = mysqli_fetch_all($specialized_categories_result, MYSQLI_ASSOC);

// Get all categories that have meals (for the "All" filter)
$meal_categories_query = "SELECT DISTINCT c.cat_id, c.c_name 
                         FROM category c
                         JOIN meal_category mc ON c.cat_id = mc.cat_id
                         JOIN meals m ON mc.meal_id = m.meal_id
                         WHERE m.cloud_kitchen_id = $kitchen_id";
$meal_categories_result = mysqli_query($conn, $meal_categories_query);
$meal_categories = mysqli_fetch_all($meal_categories_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($kitchen['business_name'] ?? 'Cloud Kitchen'); ?> - Menu</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Popup Notification Styles */
        .notification-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            align-items: center;
            transform: translateX(120%);
            transition: transform 0.3s ease-in-out;
        }
        .notification-popup.show {
            transform: translateX(0);
        }
        .notification-popup.success {
            background-color: #4CAF50;
        }
        .notification-popup.error {
            background-color: #F44336;
        }
        .notification-popup.warning {
            background-color: #FF9800;
        }
        .notification-icon {
            margin-right: 10px;
            font-size: 20px;
        }
        .close-notification {
            margin-left: 15px;
            cursor: pointer;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <?php include '..\global\navbar\navbar.php'; ?>

    <!-- Notification Popup Container -->
    <div id="notificationContainer"></div>

    <div class="container">
            <div class="nav-section">
                <a href="\NEW-TODAYS-MEAL\customer\Show_Caterers\index.php" class="nav-link">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m12 19-7-7 7-7"/>
                    </svg>
                    <span>Back to Cloud Kitchen</span>
                </a>
            </div>
            <h1 class="page-title" id="restaurantName"><?php echo htmlspecialchars($kitchen['business_name'] ?? 'Cloud Kitchen'); ?></h1>
        <!-- Customized Order Banner -->
        <?php if ($kitchen['customized_orders']): ?>
        <div class="custom-order-banner">
            <div class="banner-content">
                <div class="banner-text">
                    <svg class="users-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="m22 21-3-3m0 0a5.5 5.5 0 1 1-7.778-7.778 5.5 5.5 0 0 1 7.778 7.778z"/>
                    </svg>
                    <span>Need something special? Request a customized order!</span>
                </div> 
                <a href="\NEW-TODAYS-MEAL\customer\Custom_Order\custom-order.php?kitchen_id=<?php echo $kitchen_id; ?>">
                    <button class="custom-order-btn">Request Custom Order</button>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search and Filters -->
        <form method="GET" id="searchForm">
            <div class="search-card">
                <div class="search-container">
                    <input type="hidden" name="kitchen_id" value="<?php echo $kitchen_id; ?>">
                    <input type="text" name="search" id="searchInput" class="search-input" placeholder="Search menu items, dietary tags..." 
                          value="<?php echo htmlspecialchars($search_term); ?>">
                    <div class="price-filter">
                        <span class="filter-label">Price Range:</span>
                        <select name="price" id="priceRange" class="price-select">
                            <option value="all" <?php echo $price_range === 'all' ? 'selected' : ''; ?>>All Prices</option>
                            <option value="0-50" <?php echo $price_range === '0-50' ? 'selected' : ''; ?>>0 - 50 EGP</option>
                            <option value="50-100" <?php echo $price_range === '50-100' ? 'selected' : ''; ?>>50 - 100 EGP</option>
                            <option value="100+" <?php echo $price_range === '100+' ? 'selected' : ''; ?>>100+ EGP</option>
                        </select>
                    </div>
                    <button type="submit" style="display:none;">Search</button> <!-- Hidden submit button for form submission -->
                </div>
            </div>
        </form>

        <!-- Category Tabs -->
        <div class="category-tabs">
            <form method="GET">
                <input type="hidden" name="kitchen_id" value="<?php echo $kitchen_id; ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                <input type="hidden" name="price" value="<?php echo htmlspecialchars($price_range); ?>">
                <button type="submit" name="category" value="all" class="category-btn <?php echo $current_category === 'all' ? 'active' : ''; ?>">All</button>
                <?php foreach ($specialized_categories as $category): ?>
                    <button type="submit" name="category" value="<?php echo htmlspecialchars($category['cat_id']); ?>" 
                            class="category-btn <?php echo $current_category == $category['cat_id'] ? 'active' : ''; ?>"
                            data-category="<?php echo htmlspecialchars($category['cat_id']); ?>">
                        <?php echo htmlspecialchars($category['c_name']); ?>
                    </button>
                <?php endforeach; ?>
            </form>
        </div>

        <!-- Menu Items Grid -->
        <div class="menu-grid" id="menuGrid">
            <?php if (empty($meals)): ?>
                <div class="no-results">
                    <p>No menu items found for this cloud kitchen.</p>
                </div>
            <?php else: 
                $has_results = false;
                foreach ($meals as $meal): 
                    // Check if meal matches current filters
                    $matches_category = $current_category === 'all' || 
                                      (isset($meal['category_ids']) && strpos($meal['category_ids'], $current_category) !== false);
                    
                    $matches_price = true;
                    if ($price_range === '0-50') {
                        $matches_price = $meal['price'] <= 50;
                    } elseif ($price_range === '50-100') {
                        $matches_price = $meal['price'] > 50 && $meal['price'] <= 100;
                    } elseif ($price_range === '100+') {
                        $matches_price = $meal['price'] > 100;
                    }
                    
                    $matches_search = empty($search_term) || 
                                    stripos($meal['name'], $search_term) !== false || 
                                    stripos($meal['description'], $search_term) !== false;
                    
                    if ($matches_category && $matches_price && $matches_search):
                        $has_results = true;
                        $is_out_of_stock = $meal['stock_quantity'] <= 0;
            ?>
                  <div class="menu-item" 
                      data-category="<?php echo htmlspecialchars($meal['category_ids'] ?? ''); ?>" 
                      data-price="<?php echo htmlspecialchars($meal['price']); ?>"
                      data-tags="<?php echo htmlspecialchars($meal['dietary_tags_search'] ?? ''); ?>"
                      <?php if ($is_out_of_stock) echo 'data-stock="0"'; ?>>
                      <?php if (!empty($meal['photo'])): ?>
                        <?php $imagePath = '../../uploads/meals/' . htmlspecialchars($meal['photo']); ?>
                        <img src="<?php echo $imagePath; ?>" 
                            onerror="this.onerror=null;this.src='../../uploads/meals/68397d6a81331.png';" 
                            alt="<?php echo htmlspecialchars($meal['name']); ?>">
                    <?php else: ?>
                        <img src="../../uploads/meals/68397d6a81331.png" alt="<?php echo htmlspecialchars($meal['name']); ?>">
                    <?php endif; ?>
                      <div class="menu-content">
                          <div class="item-header">
                              <h3 class="item-name"><?php echo htmlspecialchars($meal['name']); ?></h3>
                              <?php if ($is_out_of_stock): ?>
                                  <span class="stock-badge">Out of Stock</span>
                              <?php endif; ?>
                          </div>
                          <p class="item-description"><?php echo htmlspecialchars($meal['description']); ?></p>
                          <?php if (!empty($meal['dietary_tags'])): ?>
                              <div class="dietary-tags">
                                  <?php 
                                  $tags = explode(', ', $meal['dietary_tags']);
                                  foreach ($tags as $tag): 
                                      if (!empty(trim($tag))):
                                  ?>
                                      <span class="dietary-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                  <?php 
                                      endif;
                                  endforeach; 
                                  ?>
                              </div>
                          <?php endif; ?>
                          <div class="item-footer">
                              <span class="item-price"><?php echo htmlspecialchars($meal['price']); ?> EGP</span>
                              <button class="add-to-cart-btn" 
                                      data-meal-id="<?php echo $meal['meal_id']; ?>"
                                      data-kitchen-id="<?php echo $kitchen_id; ?>">
                                  <svg class="cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                      <circle cx="9" cy="21" r="1"/>
                                      <circle cx="20" cy="21" r="1"/>
                                      <path d="m1 1 4 4 12.5 3-2.5 12H7"/>
                                  </svg>
                                  <?php echo $is_out_of_stock ? 'Add to Cart' : 'Add to Cart'; ?>
                              </button>
                          </div>
                      </div>
                </div>
                <?php endif; endforeach; 
                if (!$has_results): ?>
                    <div class="no-results">
                        <p>No menu items found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php include '..\global\footer\footer.php'; ?>


    <script src="script.js"></script>
    <script>
      if (performance.navigation.type === 1) {
            // Page was reloaded
            window.location.href = window.location.pathname + '?kitchen_id=<?php echo $kitchen_id; ?>';
        }

        // Cart AJAX functionality with popup notifications
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const mealId = this.getAttribute('data-meal-id');
                const kitchenId = this.getAttribute('data-kitchen-id');
                const isOutOfStock = this.textContent.includes('Pre-Order');
                
                fetch(`/NEW-TODAYS-MEAL/customer/Cart/add_to_cart.php?meal_id=${mealId}&kitchen_id=${kitchenId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (isOutOfStock) {
                              showNotification('Item added to cart successfully!', 'success');
                            } else {
                                showNotification('Item added to cart successfully!', 'success');
                            }
                        } else {
                            showNotification(data.message || 'Error adding item to cart', 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('An error occurred while adding to cart', 'error');
                        console.error('Error:', error);
                    });
            });
        });

        // Show notification popup
        function showNotification(message, type) {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification-popup ${type}`;
            
            // Create icon based on type
            let icon;
            switch(type) {
                case 'success':
                    icon = '<i class="fas fa-check-circle notification-icon"></i>';
                    break;
                case 'error':
                    icon = '<i class="fas fa-exclamation-circle notification-icon"></i>';
                    break;
                case 'warning':
                    icon = '<i class="fas fa-exclamation-triangle notification-icon"></i>';
                    break;
                default:
                    icon = '<i class="fas fa-info-circle notification-icon"></i>';
            }
            
            notification.innerHTML = `
                ${icon}
                ${message}
                <span class="close-notification">&times;</span>
            `;
            
            container.appendChild(notification);
            
            // Show the notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            // Close button functionality
            notification.querySelector('.close-notification').addEventListener('click', () => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            });
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }

        // Check URL for cart messages (for when page is reloaded after adding to cart)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('cart_message')) {
            const message = urlParams.get('cart_message');
            switch(message) {
                case 'added':
                    showNotification('Item added to cart successfully!', 'success');
                    break;
                case 'out_of_stock':
                    showNotification('Item added to cart successfully!', 'success');
                    break;

            }
            
            // Clean URL
            const cleanUrl = window.location.pathname + '?kitchen_id=<?php echo $kitchen_id; ?>';
            window.history.replaceState({}, document.title, cleanUrl);
        }
    </script>
</body>
</html>