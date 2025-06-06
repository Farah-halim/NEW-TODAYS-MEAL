
<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tag_name = trim($_POST['tag_name']);
        if (empty($tag_name)) {
            throw new Exception('Tag name is required');
        }

        $stmt = $pdo->prepare("INSERT INTO dietary_tags (tag_name) VALUES (?)");
        $stmt->execute([$tag_name]);
        $_SESSION['success'] = 'Tag added successfully';
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Tag already exists';
    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: manage_categories.php');
exit;
