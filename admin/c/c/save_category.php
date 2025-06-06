<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            throw new Exception('Category name is required');
        }

        // Create main category
        $stmt = $pdo->prepare("INSERT INTO category (c_name, description, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $_SESSION['admin_id']]);
        
        // Log admin action
        $log_stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, action_target, created_at) VALUES (?, ?, ?, NOW())");
        $log_stmt->execute([
            $_SESSION['admin_id'],
            'Created category',
            $name
        ]);
        
        $_SESSION['success'] = 'Category added successfully';
        
    } catch(PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $_SESSION['error'] = 'A category with this name already exists';
        } else {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: manage_categories.php');
exit;
