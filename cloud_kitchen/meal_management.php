<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../DB_connection.php';

// Get cloud kitchen ID from session or database
if (!isset($_SESSION['cloud_kitchen_id'])) {
    // If not in session, fetch from database
    $stmt = $conn->prepare("SELECT user_id FROM cloud_kitchen_owner WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // User is not a cloud kitchen owner
        header("Location: unauthorized.php");
        exit();
    }
    
    $row = $result->fetch_assoc();
    $_SESSION['cloud_kitchen_id'] = $row['user_id'];
    $stmt->close();
}

$cloud_kitchen_id = $_SESSION['cloud_kitchen_id'];

// Get categories, subcategories and dietary tags
$categories = $conn->query("SELECT * FROM category")->fetch_all(MYSQLI_ASSOC);
$subcategories = $conn->query("SELECT * FROM sub_category")->fetch_all(MYSQLI_ASSOC);
$dietary_tags = $conn->query("SELECT * FROM dietary_tags")->fetch_all(MYSQLI_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_meal':
                    $required = ['mealName', 'price', 'description'];
                    foreach ($required as $field) {
                        if (empty($_POST[$field])) {
                            throw new Exception("All required fields must be filled");
                        }
                    }

                    $photo = 'default.jpg';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                        $max_size = 2 * 1024 * 1024;
                        
                        if (!in_array($_FILES['image']['type'], $allowed_types)) {
                            throw new Exception("Only JPG, PNG, and GIF files are allowed");
                        }

                        if ($_FILES['image']['size'] > $max_size) {
                            throw new Exception("File size exceeds 2MB limit");
                        }

                        $target_dir = "../uploads/meals/";
                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0755, true);
                        }

                        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $photo = uniqid('meal_') . '.' . $ext;
                        $target_file = $target_dir . $photo;

                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                            throw new Exception("Failed to upload image");
                        }
                    }

                    $stmt = $conn->prepare("INSERT INTO meals (cloud_kitchen_id, name, description, photo, price, visible) 
                                          VALUES (?, ?, ?, ?, ?, 1)");
                    $stmt->bind_param("isssd", $cloud_kitchen_id, $_POST['mealName'], 
                                    $_POST['description'], $photo, $_POST['price']);
                    $stmt->execute();
                    $meal_id = $stmt->insert_id;

                    if (!empty($_POST['categories'])) {
                        foreach ($_POST['categories'] as $cat_id) {
                            $cat_stmt = $conn->prepare("INSERT INTO meal_category (meal_id, cat_id) VALUES (?, ?)");
                            $cat_stmt->bind_param("ii", $meal_id, $cat_id);
                            $cat_stmt->execute();
                        }
                    }

                    if (!empty($_POST['subcategories'])) {
                        foreach ($_POST['subcategories'] as $subcat_id) {
                            $sub_stmt = $conn->prepare("INSERT INTO meal_subcategory (meal_id, subcat_id) VALUES (?, ?)");
                            $sub_stmt->bind_param("ii", $meal_id, $subcat_id);
                            $sub_stmt->execute();
                        }
                    }

                    if (!empty($_POST['dietary_tags'])) {
                        foreach ($_POST['dietary_tags'] as $tag_id) {
                            $tag_stmt = $conn->prepare("INSERT INTO meal_dietary_tag (meal_id, tag_id) VALUES (?, ?)");
                            $tag_stmt->bind_param("ii", $meal_id, $tag_id);
                            $tag_stmt->execute();
                        }
                    }

                    $_SESSION['notification'] = [
                        'type' => 'success',
                        'message' => 'Meal added successfully'
                    ];
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit;

                case 'edit_meal':
                    $required = ['meal_id', 'mealName', 'price', 'description'];
                    foreach ($required as $field) {
                        if (empty($_POST[$field])) {
                            throw new Exception("All required fields must be filled");
                        }
                    }

                    $meal_id = (int)$_POST['meal_id'];
                    
                    // Get current meal data
                    $stmt = $conn->prepare("SELECT * FROM meals WHERE meal_id = ? AND cloud_kitchen_id = ?");
                    $stmt->bind_param("ii", $meal_id, $cloud_kitchen_id);
                    $stmt->execute();
                    $current_meal = $stmt->get_result()->fetch_assoc();
                    
                    if (!$current_meal) {
                        throw new Exception("Meal not found");
                    }

                    $photo = $current_meal['photo'];
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                        $max_size = 2 * 1024 * 1024;
                        
                        if (!in_array($_FILES['image']['type'], $allowed_types)) {
                            throw new Exception("Only JPG, PNG, and GIF files are allowed");
                        }

                        if ($_FILES['image']['size'] > $max_size) {
                            throw new Exception("File size exceeds 2MB limit");
                        }

                        $target_dir = "../uploads/meals/";
                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0755, true);
                        }

                        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $photo = uniqid('meal_') . '.' . $ext;
                        $target_file = $target_dir . $photo;

                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                            throw new Exception("Failed to upload image");
                        }
                        
                        // Delete old image if it's not the default
                        if ($current_meal['photo'] !== 'default.jpg') {
                            @unlink($target_dir . $current_meal['photo']);
                        }
                    }

                    $stmt = $conn->prepare("UPDATE meals SET name = ?, description = ?, photo = ?, price = ? 
                                          WHERE meal_id = ? AND cloud_kitchen_id = ?");
                    $stmt->bind_param("sssdii", $_POST['mealName'], $_POST['description'], 
                                    $photo, $_POST['price'], $meal_id, $cloud_kitchen_id);
                    $stmt->execute();

                    // Update categories
                    $conn->query("DELETE FROM meal_category WHERE meal_id = $meal_id");
                    if (!empty($_POST['categories'])) {
                        foreach ($_POST['categories'] as $cat_id) {
                            $cat_stmt = $conn->prepare("INSERT INTO meal_category (meal_id, cat_id) VALUES (?, ?)");
                            $cat_stmt->bind_param("ii", $meal_id, $cat_id);
                            $cat_stmt->execute();
                        }
                    }

                    // Update subcategories
                    $conn->query("DELETE FROM meal_subcategory WHERE meal_id = $meal_id");
                    if (!empty($_POST['subcategories'])) {
                        foreach ($_POST['subcategories'] as $subcat_id) {
                            $sub_stmt = $conn->prepare("INSERT INTO meal_subcategory (meal_id, subcat_id) VALUES (?, ?)");
                            $sub_stmt->bind_param("ii", $meal_id, $subcat_id);
                            $sub_stmt->execute();
                        }
                    }

                    // Update dietary tags
                    $conn->query("DELETE FROM meal_dietary_tag WHERE meal_id = $meal_id");
                    if (!empty($_POST['dietary_tags'])) {
                        foreach ($_POST['dietary_tags'] as $tag_id) {
                            $tag_stmt = $conn->prepare("INSERT INTO meal_dietary_tag (meal_id, tag_id) VALUES (?, ?)");
                            $tag_stmt->bind_param("ii", $meal_id, $tag_id);
                            $tag_stmt->execute();
                        }
                    }

                    $_SESSION['notification'] = [
                        'type' => 'success',
                        'message' => 'Meal updated successfully'
                    ];
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit;

                case 'delete_meal':
                    try {
                        if (empty($_POST['meal_id'])) {
                            throw new Exception("Meal ID is required");
                        }
                        
                        $meal_id = (int)$_POST['meal_id'];
                        
                        // 1. Check in order_content for active orders (not delivered or cancelled)
                        $stmt = $conn->prepare("
                            SELECT COUNT(*) as count 
                            FROM order_content oc
                            JOIN orders o ON oc.order_id = o.order_id
                            WHERE oc.meal_id = ? 
                            AND o.order_status NOT IN ('delivered', 'cancelled')
                        ");
                        $stmt->bind_param("i", $meal_id);
                        $stmt->execute();
                        $active_orders_check = $stmt->get_result()->fetch_assoc();
                        
                        if ($active_orders_check['count'] > 0) {
                            $_SESSION['notification'] = [
                                'type' => 'warning',
                                'message' => 'Cannot delete meal - it is part of active orders'
                            ];
                            header("Location: ".$_SERVER['PHP_SELF']);
                            exit;
                        }
                        
                        // 2. Check in meals_in_each_package for active packages
                        $stmt = $conn->prepare("
                            SELECT COUNT(*) as count
                            FROM meals_in_each_package mip
                            JOIN order_packages op ON mip.package_id = op.package_id
                            JOIN orders o ON op.order_id = o.order_id
                            WHERE mip.meal_id = ?
                            AND o.order_status NOT IN ('delivered', 'cancelled')
                        ");
                        $stmt->bind_param("i", $meal_id);
                        $stmt->execute();
                        $active_packages_check = $stmt->get_result()->fetch_assoc();
                        
                        if ($active_packages_check['count'] > 0) {
                            $_SESSION['notification'] = [
                                'type' => 'warning',
                                'message' => 'Cannot delete meal - it is part of active Orders'
                            ];
                            header("Location: ".$_SERVER['PHP_SELF']);
                            exit;
                        }
                        
                        // Start transaction
                        $conn->begin_transaction();
                        
                        try {
                            // First delete from all referencing tables
                            $tables_to_clean = [
                                'meal_category',
                                'meal_subcategory',
                                'meal_dietary_tag',
                                'order_content', // Explicitly clean order_content
                                'meals_in_each_package'
                            ];
                            
                            foreach ($tables_to_clean as $table) {
                                $stmt = $conn->prepare("DELETE FROM $table WHERE meal_id = ?");
                                $stmt->bind_param("i", $meal_id);
                                $stmt->execute();
                            }
                            
                            // Then delete the meal itself
                            $stmt = $conn->prepare("DELETE FROM meals WHERE meal_id = ? AND cloud_kitchen_id = ?");
                            $stmt->bind_param("ii", $meal_id, $cloud_kitchen_id);
                            $stmt->execute();
                            
                            if ($stmt->affected_rows === 0) {
                                throw new Exception("Meal not found or you don't have permission to delete it");
                            }
                            
                            $conn->commit();
                            
                            $_SESSION['notification'] = [
                                'type' => 'success',
                                'message' => 'Meal deleted successfully'
                            ];
                            header("Location: ".$_SERVER['PHP_SELF']);
                            exit;
                            
                        } catch (Exception $e) {
                            $conn->rollback();
                            throw $e;
                        }
                        
                    } catch (Exception $e) {
                        $_SESSION['notification'] = [
                            'type' => 'error',
                            'message' => $e->getMessage()
                        ];
                        header("Location: ".$_SERVER['PHP_SELF']);
                        exit;
                    }
                    break;

                case 'toggle_visibility':
                    if (empty($_POST['meal_id'])) {
                        throw new Exception("Meal ID is required");
                    }
                    
                    $meal_id = (int)$_POST['meal_id'];
                    $stmt = $conn->prepare("SELECT visible FROM meals WHERE meal_id = ? AND cloud_kitchen_id = ?");
                    $stmt->bind_param("ii", $meal_id, $cloud_kitchen_id);
                    $stmt->execute();
                    $current = $stmt->get_result()->fetch_assoc();
                    
                    if (!$current) {
                        throw new Exception("Meal not found");
                    }
                    
                    $new_visibility = $current['visible'] ? 0 : 1; // Toggle between 0 and 1
                    $stmt = $conn->prepare("UPDATE meals SET visible = ? WHERE meal_id = ? AND cloud_kitchen_id = ?");
                    $stmt->bind_param("iii", $new_visibility, $meal_id, $cloud_kitchen_id);
                    $stmt->execute();
                    
                    $_SESSION['notification'] = [
                        'type' => 'success',
                        'message' => 'Meal visibility updated'
                    ];
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit;
            }
        }
    } catch (Exception $e) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => $e->getMessage()
        ];
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// Handle search
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$stmt = $conn->prepare("SELECT * FROM meals WHERE cloud_kitchen_id = ?" . 
                      (!empty($search_query) ? " AND name LIKE ?" : ""));
                      
if (!empty($search_query)) {
    $search_param = "%" . $conn->real_escape_string($search_query) . "%";
    $stmt->bind_param("is", $cloud_kitchen_id, $search_param);
} else {
    $stmt->bind_param("i", $cloud_kitchen_id);
}

$stmt->execute();
$result = $stmt->get_result();
$meals = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check for session notification
$notification = $_SESSION['notification'] ?? null;
unset($_SESSION['notification']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="front_end/meal_management/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'global/navbar.php'; ?>
    
    <!-- Notification Container -->
    <div class="notification-container" id="notificationContainer">
        <?php if ($notification): ?>
            <div class="notification <?= $notification['type'] ?> show" data-duration="5000">
                <i class="ti ti-<?= 
                    $notification['type'] === 'success' ? 'circle-check' : 
                    ($notification['type'] === 'warning' ? 'alert-triangle' : 'alert-circle') 
                ?>"></i>
                <span><?= htmlspecialchars($notification['message']) ?></span>
                <div class="progress-bar"><div class="progress"></div></div>
            </div>
        <?php endif; ?>
    </div>

    <main class="container">
        <main class="main-content">
            <section class="content-wrapper">
                <div class="section-header">
                    <form method="GET" action="" style="display: contents;">
                        <input type="text" placeholder="Search by meal name..." class="search-input" id="mealSearch" 
                               name="search" value="<?= htmlspecialchars($search_query) ?>" />
                    </form>
                    <button class="add-meal-button" id="addMealBtn" onclick="window.location.href='?show_add_modal=1'">
                        <i class="ti ti-plus"></i>
                        <span>Add New Meal</span>
                    </button>
                </div>

                <div class="meals-grid">
                    <?php foreach ($meals as $meal): 
                        $meal_categories = $conn->query("SELECT c.c_name 
                                                       FROM meal_category mc
                                                       JOIN category c ON mc.cat_id = c.cat_id
                                                       WHERE mc.meal_id = {$meal['meal_id']}")
                                          ->fetch_all(MYSQLI_ASSOC);
                        $meal_subcategories = $conn->query("SELECT sc.subcat_name 
                                                           FROM meal_subcategory msc
                                                           JOIN sub_category sc ON msc.subcat_id = sc.subcat_id
                                                           WHERE msc.meal_id = {$meal['meal_id']}")
                                            ->fetch_all(MYSQLI_ASSOC);
                        $meal_dietary_tags = $conn->query("SELECT dt.tag_name 
                                                         FROM meal_dietary_tag mdt
                                                         JOIN dietary_tags dt ON mdt.tag_id = dt.tag_id
                                                         WHERE mdt.meal_id = {$meal['meal_id']}")
                                          ->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <div class="meal-card <?= $meal['visible'] ? '' : 'hidden-from-menu' ?>" data-meal-id="<?= $meal['meal_id'] ?>">
                        <img src="../uploads/meals/<?= htmlspecialchars($meal['photo']) ?>" 
                             alt="<?= htmlspecialchars($meal['name']) ?>" 
                             class="meal-card-image">
                        
                        <div class="meal-card-content">
                            <h3 class="meal-card-title">
                                <?= htmlspecialchars($meal['name']) ?>
                                <?php if (!$meal['visible']): ?>
                                    <span class="visibility-badge" title="Hidden from menu">
                                        <i class="ti ti-eye-off"></i>
                                    </span>
                                <?php endif; ?>
                            </h3>
                            
                            <div class="meal-card-stats">
                                <span class="meal-card-price"><?= number_format($meal['price'], 2) ?> EGP</span>
                            </div>
                            
                            <p class="meal-card-description"><?= htmlspecialchars($meal['description']) ?></p>
                            
                            <div class="meal-card-tags">
                                <?php foreach ($meal_categories as $cat): ?>
                                    <span class="tag"><?= htmlspecialchars($cat['c_name']) ?></span>
                                <?php endforeach; ?>
                                <?php foreach ($meal_subcategories as $subcat): ?>
                                    <span class="tag"><?= htmlspecialchars($subcat['subcat_name']) ?></span>
                                <?php endforeach; ?>
                                <?php foreach ($meal_dietary_tags as $diet_tag): ?>
                                    <span class="tag dietary-tag"><?= htmlspecialchars($diet_tag['tag_name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="meal-card-footer">
                                <div class="action-buttons-group">
                                    <button class="action-button edit-meal-btn" onclick="window.location.href='?edit_meal=<?= $meal['meal_id'] ?>'">
                                        <i class="ti ti-pencil"></i>
                                        Edit
                                    </button>
                                    <button class="action-button visibility-btn <?= $meal['visible'] ? 'visible' : 'hidden' ?>" 
                                            onclick="document.getElementById('toggle-form-<?= $meal['meal_id'] ?>').submit()">
                                        <i class="ti ti-eye<?= $meal['visible'] ? '' : '-off' ?>"></i>
                                        <?= $meal['visible'] ? 'Hide' : 'Show' ?>
                                    </button>
                                    <button class="action-button delete-btn" onclick="if(confirm('Are you sure you want to delete this meal?')) { document.getElementById('delete-form-<?= $meal['meal_id'] ?>').submit(); }">
                                        <i class="ti ti-trash"></i>
                                        Delete
                                    </button>
                                    <form id="toggle-form-<?= $meal['meal_id'] ?>" method="POST" action="" style="display: none;">
                                        <input type="hidden" name="action" value="toggle_visibility">
                                        <input type="hidden" name="meal_id" value="<?= $meal['meal_id'] ?>">
                                    </form>
                                    <form id="delete-form-<?= $meal['meal_id'] ?>" method="POST" action="" style="display: none;">
                                        <input type="hidden" name="action" value="delete_meal">
                                        <input type="hidden" name="meal_id" value="<?= $meal['meal_id'] ?>">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>

        <!-- Add Meal Modal -->
        <?php if (isset($_GET['show_add_modal'])): ?>
        <div class="modal active" id="addMealModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Meal</h3>
                    <button class="close-modal" aria-label="Close modal" onclick="window.location.href='?'">×</button>
                </div>
                <form id="addMealForm" class="edit-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_meal">
                    <div class="form-group">
                        <label for="newMealImage">Meal Image</label>
                        <div class="image-upload-container">
                            <img id="newMealImagePreview" src="https://placehold.co/300x200" 
                                 alt="New meal preview" class="image-preview" />
                            <input type="file" id="newMealImage" name="image" accept="image/*" required />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="newMealName">Meal Name</label>
                        <input type="text" id="newMealName" name="mealName" placeholder="Enter the name of the meal" required />
                    </div>
                    <div class="form-group">
                        <label for="newMealDescription">Description</label>
                        <textarea id="newMealDescription" name="description" rows="4" 
                                  placeholder="Describe the meal, its ingredients, and special features" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="newPrice">Price (EGP)</label>
                        <input type="number" id="newPrice" name="price" placeholder="Enter the price in EGP" step="0.01" required />
                    </div>
                    <div class="form-group">
    <label>Categories & Subcategories</label>
    <div class="categories-section">
        <div class="category-search-container">
            <input type="text" 
                   class="search-categories" 
                   placeholder="Search categories or subcategories..." 
                   id="categorySearch">
        </div>
        <div class="category-group-container">
            <?php foreach ($categories as $category): 
                $category_subcats = array_filter($subcategories, function($subcat) use ($category) {
                    return $subcat['parent_cat_id'] == $category['cat_id'];
                });
            ?>
            <div class="category-group" data-category="<?= strtolower($category['c_name']) ?>">
                <label class="tag-checkbox category-parent">
                    <input type="checkbox" 
                           name="categories[]" 
                           value="<?= $category['cat_id'] ?>"
                           <?= isset($selected_categories) && in_array($category['cat_id'], $selected_categories) ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($category['c_name']) ?></span>
                </label>
                <?php if (!empty($category_subcats)): ?>
                <div class="subcategory-list">
                    <?php foreach ($category_subcats as $subcat): ?>
                    <div class="subcategory-item" data-subcategory="<?= strtolower($subcat['subcat_name']) ?>">
                        <label class="tag-checkbox">
                            <input type="checkbox" 
                                   name="subcategories[]" 
                                   value="<?= $subcat['subcat_id'] ?>"
                                   <?= isset($selected_subcategories) && in_array($subcat['subcat_id'], $selected_subcategories) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($subcat['subcat_name']) ?></span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
                    <div class="form-group">
                        <label>Dietary Tags</label>
                        <div class="tags-container">
                            <?php foreach ($dietary_tags as $tag): ?>
                            <label class="tag-checkbox">
                                <input type="checkbox" name="dietary_tags[]" value="<?= $tag['tag_id'] ?>">
                                <span><?= htmlspecialchars($tag['tag_name']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-button" onclick="window.location.href='?'">
                            <i class="ti ti-x"></i>
                            Cancel
                        </button>
                        <button type="submit" class="save-button">
                            <i class="ti ti-device-floppy"></i>
                            Save Meal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Meal Modal -->
        <?php if (isset($_GET['edit_meal'])): 
            $meal_id = (int)$_GET['edit_meal'];
            $meal = $conn->query("SELECT * FROM meals WHERE meal_id = $meal_id AND cloud_kitchen_id = $cloud_kitchen_id")->fetch_assoc();
            
            if ($meal):
                $meal_categories = $conn->query("SELECT cat_id FROM meal_category WHERE meal_id = $meal_id")->fetch_all(MYSQLI_ASSOC);
                $meal_subcategories = $conn->query("SELECT subcat_id FROM meal_subcategory WHERE meal_id = $meal_id")->fetch_all(MYSQLI_ASSOC);
                $meal_dietary_tags = $conn->query("SELECT tag_id FROM meal_dietary_tag WHERE meal_id = $meal_id")->fetch_all(MYSQLI_ASSOC);
                
                $selected_categories = array_column($meal_categories, 'cat_id');
                $selected_subcategories = array_column($meal_subcategories, 'subcat_id');
                $selected_tags = array_column($meal_dietary_tags, 'tag_id');
        ?>
        <div class="modal active" id="editMealModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Meal</h3>
                    <button class="close-modal" aria-label="Close modal" onclick="window.location.href='?'">×</button>
                </div>
                <form id="editMealForm" class="edit-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit_meal">
                    <input type="hidden" name="meal_id" value="<?= $meal['meal_id'] ?>">
                    <div class="form-group">
                        <label for="editMealImage">Meal Image</label>
                        <div class="image-upload-container">
                            <img id="editMealImagePreview" src="../uploads/meals/<?= htmlspecialchars($meal['photo']) ?>" 
                                 alt="Meal preview" class="image-preview" />
                            <input type="file" id="editMealImage" name="image" accept="image/*" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editMealName">Meal Name</label>
                        <input type="text" id="editMealName" name="mealName" 
                               value="<?= htmlspecialchars($meal['name']) ?>" 
                               placeholder="Enter the name of the meal" required />
                    </div>
                    <div class="form-group">
                        <label for="editMealDescription">Description</label>
                        <textarea id="editMealDescription" name="description" rows="4" 
                                  placeholder="Describe the meal, its ingredients, and special features" required><?= htmlspecialchars($meal['description']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editPrice">Price (EGP)</label>
                        <input type="number" id="editPrice" name="price" 
                               value="<?= htmlspecialchars($meal['price']) ?>" 
                               placeholder="Enter the price in EGP" step="0.01" required />
                    </div>
                    <div class="form-group">
                        <label>Categories & Subcategories</label>
                        <div class="categories-section">
                            <div class="category-search-container">
                                <input type="text" 
                                       class="search-categories" 
                                       placeholder="Search categories or subcategories..." 
                                       id="editCategorySearch">
                            </div>
                            <div class="category-group-container">
                                <?php foreach ($categories as $category): 
                                    $category_subcats = array_filter($subcategories, function($subcat) use ($category) {
                                        return $subcat['parent_cat_id'] == $category['cat_id'];
                                    });
                                ?>
                                <div class="category-group" data-category="<?= strtolower($category['c_name']) ?>">
                                    <label class="tag-checkbox category-parent">
                                        <input type="checkbox" 
                                               name="categories[]" 
                                               value="<?= $category['cat_id'] ?>"
                                               <?= in_array($category['cat_id'], $selected_categories) ? 'checked' : '' ?>>
                                        <span><?= htmlspecialchars($category['c_name']) ?></span>
                                    </label>
                                    <?php if (!empty($category_subcats)): ?>
                                    <div class="subcategory-list">
                                        <?php foreach ($category_subcats as $subcat): ?>
                                        <div class="subcategory-item" data-subcategory="<?= strtolower($subcat['subcat_name']) ?>">
                                            <label class="tag-checkbox">
                                                <input type="checkbox" 
                                                       name="subcategories[]" 
                                                       value="<?= $subcat['subcat_id'] ?>"
                                                       <?= in_array($subcat['subcat_id'], $selected_subcategories) ? 'checked' : '' ?>>
                                                <span><?= htmlspecialchars($subcat['subcat_name']) ?></span>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dietary Tags</label>
                        <div class="tags-container">
                            <?php foreach ($dietary_tags as $tag): ?>
                            <label class="tag-checkbox">
                                <input type="checkbox" 
                                       name="dietary_tags[]" 
                                       value="<?= $tag['tag_id'] ?>"
                                       <?= in_array($tag['tag_id'], $selected_tags) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($tag['tag_name']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-button" onclick="window.location.href='?'">
                            <i class="ti ti-x"></i>
                            Cancel
                        </button>
                        <button type="submit" class="save-button">
                            <i class="ti ti-device-floppy"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; endif; ?>
    </main>
        <script src="front_end/meal_management/script.js"> </script>
</body>
</html>