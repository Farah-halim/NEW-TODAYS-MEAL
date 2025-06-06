
<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['category_id'])) {
            // Delete specialist category associations first
            $stmt = $pdo->prepare("DELETE FROM cloud_kitchen_specialist_category WHERE cat_id = ?");
            $stmt->execute([$_POST['category_id']]);
            
            // Delete subcategories
            $stmt = $pdo->prepare("DELETE FROM sub_category WHERE parent_cat_id = ?");
            $stmt->execute([$_POST['category_id']]);
            
            // Delete the category
            $stmt = $pdo->prepare("DELETE FROM category WHERE cat_id = ?");
            $stmt->execute([$_POST['category_id']]);
            
            $_SESSION['success'] = 'Category deleted successfully';
        } elseif (isset($_POST['subcategory_id'])) {
            $stmt = $pdo->prepare("DELETE FROM sub_category WHERE subcat_id = ?");
            $stmt->execute([$_POST['subcategory_id']]);
            
            $_SESSION['success'] = 'Subcategory deleted successfully';
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Failed to delete category: ' . $e->getMessage();
    }
}

header('Location: manage_categories.php');
exit;
