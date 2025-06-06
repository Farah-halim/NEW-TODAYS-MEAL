<?php
// Start session and check authentication
session_start();
require_once __DIR__ . '/../DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Validate kitchen_id parameter
if (!isset($_GET['kitchen_id']) || empty($_GET['kitchen_id'])) {
    // Error response with proper HTML content type
    header('Content-Type: text/html; charset=UTF-8');
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

// Sanitize and validate kitchen_id
$kitchen_id = (int)$_GET['kitchen_id'];
if ($kitchen_id <= 0) {
    die("Invalid kitchen ID");
}

try {
    // Initialize and sanitize filter parameters
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    $price_range = isset($_GET['price']) ? $_GET['price'] : 'all';
    $current_category = isset($_GET['category']) ? $_GET['category'] : 'all';
    
    // Validate filter values
    if (!in_array($price_range, ['all', 'low', 'medium', 'high'])) {
        $price_range = 'all';
    }

    // Get kitchen details using prepared statement
    $kitchen_query = "SELECT cko.business_name, cko.customized_orders 
                     FROM cloud_kitchen_owner cko 
                     WHERE cko.user_id = ? AND cko.is_approved = 1";
    $kitchen_stmt = $conn->prepare($kitchen_query);
    $kitchen_stmt->bind_param("i", $kitchen_id);
    $kitchen_stmt->execute();
    $kitchen_result = $kitchen_stmt->get_result();
    $kitchen = $kitchen_result->fetch_assoc();
    
    if (!$kitchen) {
        die("Kitchen not found or not approved");
    }

    // Build meals query with filters
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
                    WHERE m.cloud_kitchen_id = ? AND m.visible = 1";
    
    // Add search filter if specified
    if (!empty($search_term)) {
        $meals_query .= " AND (m.name LIKE ? OR m.description LIKE ?)";
    }
    
    // Add category filter if specified
    if ($current_category !== 'all') {
        $meals_query .= " AND c.cat_id = ?";
    }
    
    // Add price range filter
    switch ($price_range) {
        case 'low':
            $meals_query .= " AND m.price < 10";
            break;
        case 'medium':
            $meals_query .= " AND m.price BETWEEN 10 AND 20";
            break;
        case 'high':
            $meals_query .= " AND m.price > 20";
            break;
    }
    
    $meals_query .= " GROUP BY m.meal_id";
    
    // Prepare and execute meals query
    $meals_stmt = $conn->prepare($meals_query);
    
    // Bind parameters based on filters
    $param_types = "i";
    $params = [$kitchen_id];
    
    if (!empty($search_term)) {
        $search_param = "%{$search_term}%";
        $param_types .= "ss";
        array_push($params, $search_param, $search_param);
    }
    
    if ($current_category !== 'all') {
        $param_types .= "i";
        array_push($params, (int)$current_category);
    }
    
    $meals_stmt->bind_param($param_types, ...$params);
    $meals_stmt->execute();
    $meals_result = $meals_stmt->get_result();
    $meals = $meals_result->fetch_all(MYSQLI_ASSOC);

    // Get specialized categories
    $specialized_categories_query = "SELECT DISTINCT c.cat_id, c.c_name 
                                   FROM category c
                                   JOIN cloud_kitchen_specialist_category cksc ON c.cat_id = cksc.cat_id
                                   WHERE cksc.cloud_kitchen_id = ?";
    $spec_cat_stmt = $conn->prepare($specialized_categories_query);
    $spec_cat_stmt->bind_param("i", $kitchen_id);
    $spec_cat_stmt->execute();
    $specialized_categories_result = $spec_cat_stmt->get_result();
    $specialized_categories = $specialized_categories_result->fetch_all(MYSQLI_ASSOC);

    // Get meal categories
    $meal_categories_query = "SELECT DISTINCT c.cat_id, c.c_name 
                            FROM category c
                            JOIN meal_category mc ON c.cat_id = mc.cat_id
                            JOIN meals m ON mc.meal_id = m.meal_id
                            WHERE m.cloud_kitchen_id = ?";
    $meal_cat_stmt = $conn->prepare($meal_categories_query);
    $meal_cat_stmt->bind_param("i", $kitchen_id);
    $meal_cat_stmt->execute();
    $meal_categories_result = $meal_cat_stmt->get_result();
    $meal_categories = $meal_categories_result->fetch_all(MYSQLI_ASSOC);

    // Close statements
    $kitchen_stmt->close();
    $meals_stmt->close();
    $spec_cat_stmt->close();
    $meal_cat_stmt->close();

} catch (Exception $e) {
    // Log error and display user-friendly message
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while loading the kitchen menu. Please try again later.");
}
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
    <div id="notificationContainer"></div>

    <div class="container">
            <div class="nav-section">
    <a href="\NEW-TODAYS-MEAL\customer\Show_Caterers\index.php" class="nav-link">
        <a href="\NEW-TODAYS-MEAL\customer\Show_Caterers\index.php" 
   style="display: inline-flex; align-items: center; text-decoration: none; font-size: 1.1rem; color: #6a4125;">
    
    <svg viewBox="0 0 24 24" fill="none" stroke="saddlebrown" stroke-width="2" 
         style="width: 30px; height: 30px; margin-right: 6px;">
        <path d="m12 19-7-7 7-7"/>
    </svg>

    <span style="font-size: 1.1rem; color: #6a4125;">Back to Cloud Kitchen</span>
</a>

    </a>
</div>

            <h1 class="page-title" id="restaurantName"><?php echo htmlspecialchars($kitchen['business_name'] ?? 'Cloud Kitchen'); ?></h1>
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
                <a href="/NEW-TODAYS-MEAL/customer/Custom_Order/custom-order.php?kitchen_id=<?php echo htmlspecialchars($kitchen_id); ?>&customer_id=<?php echo htmlspecialchars($user_id); ?>" style="text-decoration: none;">
                    <button class="custom-order-btn">Request Custom Order</button>
                </a>
            </div>
        </div>
        <?php endif; ?>

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
                    <button type="submit" style="display:none;">Search</button> 
                </div>
            </div>
        </form>

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

        <div class="menu-grid" id="menuGrid">
            <?php if (empty($meals)): ?>
                <div class="no-results">
                    <p>No menu items found for this cloud kitchen.</p>
                </div>
            <?php else: 
                $has_results = false;
                foreach ($meals as $meal): 
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
                        $is_out_of_stock = $meal['stock_quantity'] <= 0; ?>

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
                                      if (!empty(trim($tag))):?>
                                      <span class="dietary-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                  <?php 
                                      endif;
                                  endforeach; ?>
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
            window.location.href = window.location.pathname + '?kitchen_id=<?php echo $kitchen_id; ?>';
        }
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
        function showNotification(message, type) {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification-popup ${type}`;
            
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
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            notification.querySelector('.close-notification').addEventListener('click', () => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            });
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }

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
            const cleanUrl = window.location.pathname + '?kitchen_id=<?php echo $kitchen_id; ?>';
            window.history.replaceState({}, document.title, cleanUrl);
        }
    </script>
</body>
</html>