<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $subcat_name = trim($_POST['subcat_name']);
        $parent_cat_id = intval($_POST['parent_cat_id']);

        if (empty($subcat_name)) {
            throw new Exception('Subcategory name is required');
        }

        if (empty($parent_cat_id)) {
            throw new Exception('Parent category is required');
        }

        // Check if parent category exists
        $check_stmt = $pdo->prepare("SELECT cat_id FROM category WHERE cat_id = ?");
        $check_stmt->execute([$parent_cat_id]);
        if (!$check_stmt->fetch()) {
            throw new Exception('Invalid parent category selected');
        }

        // Insert subcategory
        $stmt = $pdo->prepare("INSERT INTO sub_category (subcat_name, admin_id, parent_cat_id) VALUES (?, ?, ?)");
        $stmt->execute([$subcat_name, $_SESSION['admin_id'], $parent_cat_id]);
        
        // Log admin action
        $log_stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, action_target, created_at) VALUES (?, ?, ?, NOW())");
        $log_stmt->execute([
            $_SESSION['admin_id'],
            'Created subcategory',
            $subcat_name
        ]);
        
        $_SESSION['success_categories'] = 'Subcategory added successfully';
        
    } catch(PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $_SESSION['error_categories'] = 'A subcategory with this name already exists in this category';
        } else {
            $_SESSION['error_categories'] = 'Database error: ' . $e->getMessage();
        }
    } catch(Exception $e) {
        $_SESSION['error_categories'] = $e->getMessage();
    }
}

header('Location: manage_categories.php');
exit; 